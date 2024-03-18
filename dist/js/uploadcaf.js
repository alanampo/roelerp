let original = null;
$(document).ready(function () {
  document.querySelectorAll(".drop-zone__input").forEach((inputElement) => {
    const dropZoneElement = inputElement.closest(".drop-zone");

    dropZoneElement.addEventListener("click", (e) => {
      inputElement.click();
    });

    inputElement.addEventListener("change", (e) => {
      currentCAF = null;
      if (inputElement.files.length) {
        updateThumbnail(dropZoneElement, inputElement.files[0]);
      }
    });

    dropZoneElement.addEventListener("dragover", (e) => {
      e.preventDefault();
      dropZoneElement.classList.add("drop-zone--over");
    });

    ["dragleave", "dragend"].forEach((type) => {
      dropZoneElement.addEventListener(type, (e) => {
        dropZoneElement.classList.remove("drop-zone--over");
      });
    });

    dropZoneElement.addEventListener("drop", (e) => {
      e.preventDefault();

      if (e.dataTransfer.files.length) {
        inputElement.files = e.dataTransfer.files;
        updateThumbnail(dropZoneElement, e.dataTransfer.files[0]);
      }

      dropZoneElement.classList.remove("drop-zone--over");
    });
    original = $(".drop-zone").html();
  });

  /**
   * Updates the thumbnail on a drop zone element.
   *
   * @param {HTMLElement} dropZoneElement
   * @param {File} file
   */
  function updateThumbnail(dropZoneElement, file) {
    let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");

    // First time - remove the prompt
    if (dropZoneElement.querySelector(".drop-zone__prompt")) {
      $(dropZoneElement).find(".drop-zone__prompt").addClass("d-none");
    }

    // First time - there is no thumbnail element, so lets create it
    if (!thumbnailElement) {
      thumbnailElement = document.createElement("div");
      thumbnailElement.classList.add("drop-zone__thumb");
      dropZoneElement.appendChild(thumbnailElement);
    }

    thumbnailElement.dataset.label = file.name;
    $(".folio-info").addClass("d-none")
    // Show thumbnail for image files
    if (file.type.startsWith("image/")) {
      const reader = new FileReader();

      reader.readAsDataURL(file);
      reader.onload = () => {
        thumbnailElement.style.backgroundImage = `url('${reader.result}')`;
      };
    } else {
      thumbnailElement.style.backgroundImage = null;
      const reader = new FileReader();

      reader.readAsText(file);
      reader.onload = () => {
        try {
          currentCAF = null;
            $(".label-folio-fecha,.label-folio-rango,.label-folio-cantidad").html("")
          var parser = new DOMParser();
          var doc = parser.parseFromString(reader.result, "application/xml");
          //console.log(doc);
          const rangoD =
            doc.getElementsByTagName("RNG")[0].childNodes[0].innerHTML;
          const rangoH =
            doc.getElementsByTagName("RNG")[0].childNodes[1].innerHTML;
          const fechaA = doc.getElementsByTagName("FA")[0].innerHTML;

          const tipoFolio = doc.getElementsByTagName("TD")[0].innerHTML;

          if (fechaA && fechaA.length && rangoD && rangoD.length && tipoFolio && tipoFolio.length){
            $(".label-folio-tipo").html(tipoFolio)
            $(".label-folio-fecha").html(fechaA)
            $(".label-folio-rango").html(`${rangoD} a ${rangoH}`)
            $(".label-folio-cantidad").html(parseInt(rangoH) - parseInt(rangoD) + 1)
            $(".folio-info").removeClass("d-none")
            currentCAF = {
              tipoFolio: tipoFolio,
              fechaA: fechaA,
              rangoD: rangoD,
              rangoH: rangoH,
              data: reader.result
            };
          }
          
                    

        } catch (error) {
          
        }
      };
    }
  }
});

function clearInput(){
  const inputElement = $("#input-caf");
  inputElement.val("")
  if ($(".drop-zone__thumb")){
    $(".drop-zone__thumb").remove();
  }
  
  $(".drop-zone__prompt").removeClass("d-none")

}