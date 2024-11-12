<div class="container">
    <h1>Importar Múltiples Archivos de Datos de Calificaciones</h1>

    <form id="import-form" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Selecciona Archivos Excel o CSV (máximo 100)</label>
            <input type="file" name="files[]" id="files" class="form-control" multiple required>
        </div>

        <button type="button" class="btn btn-primary" id="start-upload">Comenzar Importación</button>
    </form>

    <div id="upload-status" class="mt-3">
        <h4>Progreso de la Importación</h4>
        <ul id="status-list"></ul>
    </div>
</div>

<script>
document.getElementById('start-upload').onclick = function () {
    let files = document.getElementById('files').files;
    let statusList = document.getElementById('status-list');

    if (files.length > 100) {
        alert("Por favor selecciona un máximo de 100 archivos.");
        return;
    }

    // Inicializa el progreso
    statusList.innerHTML = '';

    // Procesa cada archivo uno por uno
    Array.from(files).forEach((file, index) => {
        let formData = new FormData();
        formData.append('file', file);
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('fileIndex', index + 1); // Para identificar el archivo en la respuesta

        // Crear una entrada en la lista de progreso
        let statusItem = document.createElement('li');
        statusItem.id = `status-${index + 1}`;
        statusItem.innerText = `Subiendo archivo ${index + 1} de ${files.length}: ${file.name}`;
        statusList.appendChild(statusItem);

        // Subir el archivo usando fetch API
        fetch('{{ route('datos.import.file') }}', {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              document.getElementById(`status-${data.fileIndex}`).innerText = `Archivo ${data.fileIndex}: ${file.name} - ${data.message}`;
          })
          .catch(error => {
              document.getElementById(`status-${index + 1}`).innerText = `Archivo ${index + 1}: ${file.name} - Error en la carga`;
          });
    });
};
</script>
