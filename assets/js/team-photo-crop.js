(function () {
  const fileInput = document.getElementById('teamPhotoInput');
  const modal = document.getElementById('teamCropModal');
  if (!fileInput || !modal || typeof Cropper === 'undefined') return;

  const image = document.getElementById('teamCropImage');
  const preview = document.getElementById('teamPhotoPreview');
  const previewImg = document.getElementById('teamPhotoPreviewImg');
  const btnCancel = document.getElementById('teamCropCancel');
  const btnApply = document.getElementById('teamCropApply');
  const btnClear = document.getElementById('teamPhotoClear');
  const form = fileInput.closest('form');

  let cropper = null;
  let objectUrl = null;

  function releaseUrl() {
    if (objectUrl) {
      URL.revokeObjectURL(objectUrl);
      objectUrl = null;
    }
  }

  function closeModal(clearFile) {
    if (clearFile === undefined) clearFile = true;
    modal.classList.add('hidden');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('overflow-hidden');
    if (cropper) {
      cropper.destroy();
      cropper = null;
    }
    releaseUrl();
    image.removeAttribute('src');
    if (clearFile) {
      fileInput.value = '';
      fileInput.removeAttribute('data-cropped');
    }
  }

  function openModal(file) {
    releaseUrl();
    objectUrl = URL.createObjectURL(file);
    image.src = objectUrl;
    modal.classList.remove('hidden');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('overflow-hidden');

    function initCropper() {
      if (cropper) cropper.destroy();
      cropper = new Cropper(image, {
        aspectRatio: 1,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 1,
        responsive: true,
        background: false,
      });
    }

    if (image.complete) {
      initCropper();
    } else {
      image.onload = initCropper;
    }
  }

  function setPreviewFromCanvas(canvas) {
    previewImg.src = canvas.toDataURL('image/jpeg', 0.92);
    preview.classList.remove('hidden');
    if (btnClear) btnClear.classList.remove('hidden');
  }

  function canvasToFile(canvas) {
    return new Promise(function (resolve) {
      canvas.toBlob(function (blob) {
        if (!blob) {
          resolve(null);
          return;
        }
        const name = 'team-photo-' + Date.now() + '.jpg';
        resolve(new File([blob], name, { type: 'image/jpeg' }));
      }, 'image/jpeg', 0.92);
    });
  }

  async function applyCrop() {
    if (!cropper) return;
    const canvas = cropper.getCroppedCanvas({
      width: 512,
      height: 512,
      imageSmoothingEnabled: true,
      imageSmoothingQuality: 'high',
    });
    if (!canvas) return;

    const file = await canvasToFile(canvas);
    if (!file) return;

    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    fileInput.setAttribute('data-cropped', '1');

    const pathInput = form && form.querySelector('input[name="photo_path"]');
    if (pathInput) pathInput.value = '';

    const currentWrap = document.getElementById('teamCurrentPhotoWrap');
    if (currentWrap) currentWrap.classList.add('hidden');

    setPreviewFromCanvas(canvas);
    closeModal(false);
  }

  fileInput.addEventListener('change', function () {
    const file = fileInput.files && fileInput.files[0];
    if (!file) return;
    if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
      alert('Please choose a JPG, PNG, or WebP image.');
      fileInput.value = '';
      return;
    }
    openModal(file);
  });

  btnCancel.addEventListener('click', closeModal);
  document.getElementById('teamCropCancelFooter')?.addEventListener('click', closeModal);
  btnApply.addEventListener('click', applyCrop);
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });

  if (btnClear) {
    btnClear.addEventListener('click', function () {
      fileInput.value = '';
      fileInput.removeAttribute('data-cropped');
      preview.classList.add('hidden');
      previewImg.removeAttribute('src');
      btnClear.classList.add('hidden');
      const pathInput = form && form.querySelector('input[name="photo_path"]');
      const originalPath = pathInput && pathInput.getAttribute('data-original');
      if (pathInput && originalPath) pathInput.value = originalPath;
      const currentWrap = document.getElementById('teamCurrentPhotoWrap');
      if (currentWrap && originalPath) currentWrap.classList.remove('hidden');
    });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
      closeModal();
    }
  });

  if (form) {
    form.addEventListener('submit', function (e) {
      if (fileInput.getAttribute('data-cropped') === '1' && (!fileInput.files || !fileInput.files.length)) {
        e.preventDefault();
        alert('The cropped photo was lost. Please choose the image again and click “Use cropped photo”.');
      }
    });
  }
})();
