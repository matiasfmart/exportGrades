$(document).ready(function() {
    $('.settingsform #admin-daily_hour').hide();
    $('.settingsform #admin-weekly_day,.settingsform #admin-weekly_hour').hide();
    
    function updateSettingsDisplay() {
        const selectedFrequency = $('#id_s_mod_exportgrades_export_frequency').val();
        if (selectedFrequency === 'daily') {
            console.log("Entre por el if de daily");
            $('.settingsform #admin-daily_hour').show();
            $('.settingsform #admin-weekly_day').hide();
            $('.settingsform #admin-weekly_hour').hide();
            $('.settingsform #admin-monthly_day').hide();
            $('.settingsform #admin-monthly_hour').hide();
            console.log("Entre a la daily!");
        } else if (selectedFrequency === 'weekly') {
            $('.settingsform #admin-daily_hour').hide();
            $('.settingsform #admin-weekly_day').show();
            $('.settingsform #admin-weekly_hour').show();
            $('.settingsform #admin-monthly_day').hide();
            $('.settingsform #admin-monthly_hour').hide();
            console.log("Entre a la weekly!");
        } else {
            $('.settingsform #admin-daily_hour').hide();
            $('.settingsform #admin-weekly_day').hide();
            $('.settingsform #admin-weekly_hour').hide();
            $('.settingsform #admin-monthly_day').show();
            $('.settingsform #admin-monthly_hour').show();
        }
    }

    updateSettingsDisplay();
    $('#id_s_mod_exportgrades_export_frequency').change(updateSettingsDisplay);
});
