document.addEventListener("DOMContentLoaded", function () {
  const fileInput = document.getElementById("product_image");
  const imagePreview = document.getElementById("image_preview");
  const uploadPrompt = document.getElementById("upload_prompt");
  const previewActions = document.getElementById("preview_actions");
  const removeBtn = document.getElementById("remove_image");

  // Show preview when a file is selected
  fileInput.addEventListener("change", function () {
    const file = this.files[0];

    if (file) {
      // Optional: Check file size (e.g., 2MB limit)
      if (file.size > 2 * 1024 * 1024) {
        alert("File size exceeds 2MB limit.");
        this.value = ""; // Reset file input
        return;
      }

      // Generate temporary URL for the selected file
      const objectUrl = URL.createObjectURL(file);
      imagePreview.src = objectUrl;

      // Show image and remove action, hide prompt text
      imagePreview.classList.remove("d-none");
      previewActions.classList.remove("d-none");
      previewActions.classList.add("d-flex");
      uploadPrompt.classList.add("d-none");
    }
  });

  // Clear file and restore original placeholder UI when 'Remove' is clicked
  removeBtn.addEventListener("click", function () {
    fileInput.value = ""; // Clear file input
    imagePreview.src = "#";

    imagePreview.classList.add("d-none");
    previewActions.classList.add("d-none");
    previewActions.classList.remove("d-flex");
    uploadPrompt.classList.remove("d-none");
  });
});
