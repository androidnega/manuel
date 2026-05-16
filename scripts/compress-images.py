#!/usr/bin/env python3
"""Compress images to <=320KB; preserve aspect ratio (no stretch)."""

from __future__ import annotations

import io
import json
import os
from pathlib import Path

from PIL import Image, ImageOps

MAX_BYTES = 320 * 1024
ROOT = Path(__file__).resolve().parents[1]
EXTS = {'.png', '.jpg', '.jpeg', '.webp', '.gif'}


def has_alpha(img: Image.Image) -> bool:
    if img.mode in ('RGBA', 'LA'):
        return img.getextrema()[3][0] < 255 if img.mode == 'RGBA' else True
    if img.mode == 'P':
        return 'transparency' in img.info
    return False


def resized(img: Image.Image, scale: float) -> Image.Image:
    if scale >= 0.999:
        return img.copy()
    w, h = img.size
    nw = max(1, int(w * scale))
    nh = max(1, int(h * scale))
    return img.resize((nw, nh), Image.Resampling.LANCZOS)


def encode_candidates(img: Image.Image, allow_jpeg: bool) -> list[tuple[str, bytes]]:
    out: list[tuple[str, bytes]] = []
    alpha = has_alpha(img)

    if allow_jpeg and not alpha:
        for q in range(90, 35, -5):
            buf = io.BytesIO()
            img.convert('RGB').save(buf, 'JPEG', quality=q, optimize=True, progressive=True)
            out.append(('.jpg', buf.getvalue()))

    for q in range(90, 40, -5):
        buf = io.BytesIO()
        img.save(buf, 'WEBP', quality=q, method=6)
        out.append(('.webp', buf.getvalue()))

    buf = io.BytesIO()
    save_img = img.convert('RGBA') if img.mode not in ('RGB', 'RGBA', 'L') else img
    save_img.save(buf, 'PNG', optimize=True, compress_level=9)
    out.append(('.png', buf.getvalue()))

    return out


def pick_best_under_limit(candidates: list[tuple[str, bytes]]) -> tuple[str, bytes] | None:
    ok = [(ext, data) for ext, data in candidates if len(data) <= MAX_BYTES]
    if not ok:
        return None
    return max(ok, key=lambda x: len(x[1]))


def compress_file(path: Path) -> dict:
    before = path.stat().st_size
    if before <= MAX_BYTES:
        return {'path': str(path), 'before': before, 'after': before, 'action': 'ok'}

    img = ImageOps.exif_transpose(Image.open(path))
    orig_ext = path.suffix.lower()
    # Photos / posters without transparency can use JPEG even if stored as PNG
    allow_jpeg = orig_ext in ('.jpg', '.jpeg') or (
        orig_ext == '.png' and not has_alpha(img) and path.name not in ('favicon.png', 'main-logo.png', 'dark-logo.png')
    )

    chosen: tuple[str, bytes] | None = None
    for scale in (1.0, 0.9, 0.8, 0.7, 0.6, 0.5, 0.42, 0.35, 0.28, 0.22, 0.18, 0.14):
        frame = resized(img, scale)
        chosen = pick_best_under_limit(encode_candidates(frame, allow_jpeg))
        if chosen:
            break

    if not chosen:
        frame = resized(img, 0.12)
        all_c = encode_candidates(frame, allow_jpeg)
        chosen = min(all_c, key=lambda x: len(x[1]))

    ext, data = chosen
    if len(data) > MAX_BYTES:
        raise RuntimeError(f'Cannot reach {MAX_BYTES} bytes: {path} ({len(data)} bytes)')

    # Prefer original extension when an under-limit match exists
    same_ext = [(e, d) for e, d in encode_candidates(resized(img, 1.0), allow_jpeg) if e == orig_ext and len(d) <= MAX_BYTES]
    if not same_ext:
        for scale in (0.9, 0.8, 0.7, 0.6, 0.5):
            same_ext = [
                (e, d)
                for e, d in encode_candidates(resized(img, scale), allow_jpeg)
                if e == orig_ext and len(d) <= MAX_BYTES
            ]
            if same_ext:
                ext, data = max(same_ext, key=lambda x: len(x[1]))
                break

    out_path = path
    if ext != orig_ext:
        out_path = path.with_suffix(ext)
        if out_path.exists() and out_path != path:
            out_path.unlink()
        if path.exists():
            path.unlink()

    out_path.write_bytes(data)
    after = out_path.stat().st_size
    return {
        'path': str(out_path.relative_to(ROOT)),
        'old': str(path.relative_to(ROOT)) if out_path != path else None,
        'before': before,
        'after': after,
        'ext': ext,
        'action': 'compressed',
    }


def main() -> None:
    results = []
    for dirpath, _, names in os.walk(ROOT):
        if '/.git' in dirpath or dirpath.endswith('.git'):
            continue
        for name in names:
            p = Path(dirpath) / name
            if p.suffix.lower() not in EXTS:
                continue
            r = compress_file(p)
            results.append(r)
            if r['action'] == 'ok':
                print(f"OK  {r['path']} ({r['before'] // 1024}KB)")
            else:
                old = f" (was {r['old']})" if r.get('old') else ''
                print(f"    {r['path']}{old}: {r['before'] // 1024}KB → {r['after'] // 1024}KB")

    (ROOT / 'scripts' / 'compress-manifest.json').write_text(json.dumps(results, indent=2))
    print(f"\nDone. {len(results)} files processed.")


if __name__ == '__main__':
    main()
