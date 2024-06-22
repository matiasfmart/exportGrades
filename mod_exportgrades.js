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
