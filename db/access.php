<?php
/**


*El archivo access.php en un plugin de Moodle se utiliza para definir los permisos de acceso específicos del plugin.
*Este archivo te permite controlar quién puede realizar acciones específicas relacionadas con tu plugin, 
*como ver una actividad, editarla o administrarla.

*En el contexto de tu plugin exportgrades, el archivo access.php podría utilizarse para
*definir permisos relacionados con la exportación de calificaciones, como quién puede ver las 
*calificaciones exportadas, quién puede configurar la exportación, etc.




* 
 * defined('MOODLE_INTERNAL') || die();
 * 
 * 
 * 
 * 
 * 
 *  $capabilities = array(
 *       'mod/exportGrades:export' => array(
 *          'riskbitmask' => RISK_DATALOSS,
 *          'captype' => 'write',
 *          'contextlevel' => CONTEXT_COURSE,
 *          'archetypes' => array(
 *              'teacher' => CAP_ALLOW,
 *              'editingteacher' => CAP_ALLOW,
 *              'manager' => CAP_ALLOW
 *      )
 *  ),
*   );
*  ?>
 */