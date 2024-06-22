document.addEventListener('DOMContentLoaded', function () {
    // Obtiene referencia a los selectores de curso y grupo por su ID
    var courseSelect = document.getElementById('id_mod_exportgrades_courseid');
    var groupSelect = document.getElementById('id_mod_exportgrades_group');
    var usersContainer = document.getElementById('users-dropdown-container'); // Contenedor donde se actualizarán los usuarios

    // Validar la existencia de los elementos
    if (!courseSelect) {
        console.error('Elemento con ID "id_mod_exportgrades_courseid" no encontrado.');
        return;
    }
    if (!groupSelect) {
        console.error('Elemento con ID "id_mod_exportgrades_group" no encontrado.');
        return;
    }
    if (!usersContainer) {
        console.error('Elemento con ID "users-dropdown-container" no encontrado.');
        return;
    }

    // Añadir manejadores de eventos
    courseSelect.addEventListener('change', updateUsersList);
    groupSelect.addEventListener('change', updateUsersList);

    function updateUsersList() {
        var courseid = courseSelect.value;
        var groupid = groupSelect.value;

        // Realizar una solicitud AJAX para obtener los usuarios
        fetch('/mod/exportgrades/ajax/get_users.php?courseid=' + courseid + '&groupid=' + groupid)
            .then(response => response.text())
            .then(data => {
                // Actualizar el contenido del contenedor de usuarios
                usersContainer.innerHTML = data;
            })
            .catch(error => console.error('Error en la solicitud AJAX:', error));
    }
});
