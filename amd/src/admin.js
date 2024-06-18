define(['jquery'], function($) {
    return {
        init: function() {
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

            $(document).ready(function() {
                $('#id_s_mod_exportgrades_export_frequency').change(updateVisibility);
                updateVisibility(); // Initialize on document ready
            });
        }
    };
});
