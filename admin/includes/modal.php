<?php /*
Reusable modal component for admin actions. Usage:
Include this file and use JS to set modal title/content and show/hide the modal.
*/ ?>
<div id="adminModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 hidden">
  <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-8 relative fade-in" style="animation-delay:0.1s">
    <button onclick="closeAdminModal()" class="absolute top-3 right-3 text-gray-400 hover:text-primary text-2xl">&times;</button>
    <h2 id="adminModalTitle" class="text-xl font-bold text-primary mb-4">Modal Title</h2>
    <div id="adminModalContent" class="mb-6 text-gray-700">Modal content goes here.</div>
    <div class="flex justify-end space-x-4">
      <button id="adminModalCancel" class="px-4 py-2 rounded-button bg-gray-200 text-gray-700 hover:bg-gray-300 transition">Cancel</button>
      <button id="adminModalConfirm" class="px-4 py-2 rounded-button bg-primary text-white hover:bg-primary/80 transition">Confirm</button>
    </div>
  </div>
</div>
<script>
function showAdminModal(title, content, onConfirm) {
  console.log('showAdminModal called with:', {title, content: content.substring(0, 50) + '...', onConfirm: !!onConfirm});
  document.getElementById('adminModalTitle').textContent = title;
  document.getElementById('adminModalContent').innerHTML = content;
  document.getElementById('adminModal').classList.remove('hidden');
  const confirmBtn = document.getElementById('adminModalConfirm');
  const cancelBtn = document.getElementById('adminModalCancel');
  console.log('Modal elements found:', {confirmBtn: !!confirmBtn, cancelBtn: !!cancelBtn});
  function cleanup() {
    document.getElementById('adminModal').classList.add('hidden');
    confirmBtn.removeEventListener('click', confirmHandler);
    cancelBtn.removeEventListener('click', cleanup);
  }
  function confirmHandler() {
    console.log('Confirm button clicked');
    if (onConfirm) onConfirm();
    cleanup();
  }
  function cancelHandler() {
    console.log('Cancel button clicked');
    cleanup();
  }
  confirmBtn.addEventListener('click', confirmHandler);
  cancelBtn.addEventListener('click', cancelHandler);
}
function closeAdminModal() {
  document.getElementById('adminModal').classList.add('hidden');
}
</script> 