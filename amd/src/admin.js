define(['jquery'], function ($) {
    return {
        init: function () {
            // Función para actualizar la visibilidad de opciones según la frecuencia seleccionada
            function updateVisibility() {
                var selectedFrequency = $('#id_s_mod_exportgrades_export_frequency').val();
                $('.daily-options, .weekly-options, .monthly-options').addClass('hidden');
                if (selectedFrequency === 'daily') {
                    $('.daily-options').removeClass('hidden');
                } else if (selectedFrequency === 'weekly') {
                    $('.weekly-options').removeClass('hidden');
                } else if (selectedFrequency === 'monthly') {
                    $('.monthly-options').removeClass('hidden');
                }
            }

            // Función para actualizar la visualización de configuraciones según la frecuencia seleccionada
            function updateSettingsDisplay() {
                const selectedFrequency = $('#id_s_mod_exportgrades_export_frequency').val();
                if (selectedFrequency === 'daily') {
                    $('.settingsform #admin-daily_hour').show();
                    $('.settingsform #admin-weekly_day').hide();
                    $('.settingsform #admin-weekly_hour').hide();
                    $('.settingsform #admin-monthly_day').hide();
                    $('.settingsform #admin-monthly_hour').hide();
                } else if (selectedFrequency === 'weekly') {
                    $('.settingsform #admin-daily_hour').hide();
                    $('.settingsform #admin-weekly_day').show();
                    $('.settingsform #admin-weekly_hour').show();
                    $('.settingsform #admin-monthly_day').hide();
                    $('.settingsform #admin-monthly_hour').hide();
                } else {
                    $('.settingsform #admin-daily_hour').hide();
                    $('.settingsform #admin-weekly_day').hide();
                    $('.settingsform #admin-weekly_hour').hide();
                    $('.settingsform #admin-monthly_day').show();
                    $('.settingsform #admin-monthly_hour').show();
                }
            }

            // Evento para actualizar la visibilidad al cambiar la frecuencia de exportación
            $(document).ready(function () {
                $('#id_s_mod_exportgrades_export_frequency').change(function () {
                    updateVisibility();
                    updateSettingsDisplay();
                });

                // Inicializar visibilidad y configuraciones al cargar el documento
                updateVisibility();
                updateSettingsDisplay();

                console.log('AMD module initialized.');
            });
        }
    };
});
