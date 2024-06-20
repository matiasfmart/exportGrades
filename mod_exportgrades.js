// En mod_exportgrades.js
/*
define(['jquery'], function ($) {
    return {
        init: function () {
            // Manejar el cambio en el desplegable de grupos
            $('#id_mod_exportgrades_group').on('change', function () {
                var selectedGroup = $(this).val();

                // Realizar la llamada AJAX para obtener los usuarios del grupo seleccionado
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php',  // Ajusta la ruta según sea necesario
                    data: {
                        action: 'get_users_by_group',
                        groupid: selectedGroup
                    },
                    success: function (response) {
                        // Limpiar y actualizar el desplegable de usuarios
                        var userSelect = $('#id_mod_exportgrades_user');
                        userSelect.empty();
                        userSelect.append($('<option>', {
                            value: '',
                            text: allUsersOptionText  // Utilizar la variable JavaScript definida en settings.php
                        }));

                        // Agregar opciones de usuarios obtenidos del servidor
                        $.each(response.users, function (id, name) {
                            userSelect.append($('<option>', {
                                value: id,
                                text: name
                            }));
                        });
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al obtener usuarios:', error);
                    }
                });
            });
        }
    };
});
*/

// mod_exportgrades.js

define(['jquery'], function($) {
    return {
        init: function() {
            $('#id_courseid, #id_group').change(function() {
                var courseid = $('#id_courseid').val();
                var groupid = $('#id_group').val();

                // Realizar la solicitud AJAX para obtener los usuarios
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php',  // Ajusta la ruta según sea necesario
                    data: {
                        action: 'get_users_by_course_and_group',
                        courseid: courseid,
                        groupid: groupid
                    },
                    success: function(data) {
                        // Actualizar el desplegable de usuarios con la respuesta
                        $('#id_user').html(data);
                    }
                });
            });
        }
    };
});
