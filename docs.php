<?php
require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
global $PAGE, $OUTPUT;

// Establecer el contexto. Sustituye $modulecontext por el contexto adecuado para tu página.
$context = context_system::instance(); // O el contexto apropiado para tu página
$PAGE->set_context($context);

// Establecer la URL de la página
$PAGE->set_url('/mod/exportgrades/docs.php');

$PAGE->set_title("ExportGrades");
$PAGE->set_heading("Quickstart Guide");

echo $OUTPUT->header();
?>
    <!-- Load Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Custom CSS -->
    <style>
        .main-color {
            color: #f98012 !important;
        }
    </style>

    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h2 class="text-black p-3">Lista de distribución</h2>
            </div>
            <div class="col-12">
                <p class="text-black p-3">
                    Este manual está diseñado para guiar a todos los usuarios que interactúan con nuestro plugin para
                    Moodle. Se
                    ha estructurado pensando en proporcionar la información más relevante y útil para cada grupo
                    específico
                    de
                    usuarios, asegurando que puedan maximizar las funcionalidades del plugin de manera
                    efectiva.
                </p>
            </div>
            <div class="col-12">
                <ul class="text-black p-3"> Usuarios Finales:
                    <li>Organizaciones y Empresas.</li>
                    <li>Personas individuales: Personas que sean propietarias de uno o varios cursos.</li>
                </ul>
                
            </div>

            <div class="col-6 mt-2">
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Cargo</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $intgrantes = [
                        ['nombre' => 'Matías Martinez', 'cargo' => 'Dev'],
                        ['nombre' => 'Matías Broggia', 'cargo' => 'Dev'],
                        ['nombre' => 'Esteban Lopez Fain Binda', 'cargo' => 'PM'],
                        ['nombre' => 'Noelia Taboada Vega', 'cargo' => 'Dev'],
                        ['nombre' => 'Marcela Banega', 'cargo' => 'Dev'],
                        ['nombre' => 'Maximiliano Perchik', 'cargo' => 'Dev'],
                    ];
                    $counter = 1;
                    foreach ($intgrantes as $intgrante) {
                        echo "<tr>";
                        echo "<th scope='row'>{$counter}</th>";
                        echo "<td>{$intgrante['nombre']}</td>";
                        echo "<td>{$intgrante['cargo']}</td>";
                        echo "</tr>";
                        $counter++;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="col-12">
                <h3>Índice</h3>
                <ul class="list-unstyled">
                    <li><a href="#intro">Introducción</a></li>
                    <li><a href="#objective">Objetivo del manual</a></li>
                    <li><a href="#installation">Guía de instalación del Plugin</a></li>
                    <li><a href="#create-activity">Cómo crear una actividad del Plugin</a></li>
                    <li><a href="#config-plugin">Cómo configurar el Plugin</a></li>
                    <li><a href="#fill-fields">Cómo rellenar los campos</a></li>
                    <li><a href="#check-install">Cómo saber si el plugin está bien instalado</a></li>
                    <li><a href="#faq">Preguntas Frecuentes</a></li>
                </ul>
            </div>

            <div class="col-12 mt-4" id="intro">
                <h3>Introducción</h3>
            </div>
            <div class="col-12 mt-4" id="objective">
                <h3>Objetivo del manual</h3>
                <p class="text-black p-3">Este manual ha sido creado con el objetivo de proporcionar una guía
                    completa y detallada sobre el uso de nuestro plugin. <br> <br>
                    Este manual tiene como propósito: <br> <br>

                    <b>Orientar en la navegación y funcionamiento:</b> <br>
                    Ofrecer instrucciones claras sobre configuraciones y como poder acceder a las funcionalidades del
                    plugin. <br> <br>

                    <b>Proporcionar Soporte y Asistencia:</b> <br>
                    Actuar como una fuente de consulta para responder preguntas comunes sobre el plugin
                    y cómo contactar con el equipo de soporte en caso de necesitar asistencia.<br> <br>

                    <b> Mejorar la experiencia del Usuario:</b> <br>
                    Mejorar la experiencia del usuario al proporcionar informacion que permita aprovechar
                    al máximo las características y beneficios del plugin.
                </p>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mt-4" id="installation">
                <h3>Guía de instalación del Plugin</h3>
            </div>
            <div class="col-12">
                <p class="text-black ">
                    <a href="https://drive.google.com/drive/u/0/folders/1c0jUUwTUHfi3m4pfSi0MP2jD2L2JmSAP"
                       target="_blank">https://drive.google.com/drive/u/0/folders/1c0jUUwTUHfi3m4pfSi0MP2jD2L2JmSAP</a>
                </p>
                <ol>
                    <li>Descargar el archivo ZIP que se encuentra en este link de drive.</li>
                    <li>Colocar el archivo ZIP dentro de su plataforma moodle en la carpeta /mod.</li>
                    <li>Descargar el archivo ZIP que se encuentra en este link de drive.</li>
                    <li>Extraer los archivos.</li>
                    <li>Volver al Moodle para continuar con la Descarga.</li>
                    <li>Seguir los pasos de instalacion, solo hay que darle click al boton de "Continuar".</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-12 mt-4" id="create-activity">
                <h3>Cómo crear una actividad del Plugin</h3>
                <ol>
                    <li>Ir a nuestro curso</li>
                    <li>Activar el “Edit mode” arriba a la derecha</li>
                    <img src='https://i.postimg.cc/Hk54HrNc/image11.png' border='0' alt='image11'/>
                    <li class="mt-2">agregar la actividad a tu curso</li>
                    <img src="https://i.postimg.cc/qMD228PC/image5.png" alt="image5"/>
                    <li class="mt-2">Ponerle nombre a la actividad</li>
                    <img src="https://i.postimg.cc/SNH7w6gb/image10.png" alt="image10"/>
                </ol>
            </div>

            <div class="col-12 mt-4" id="config-plugin">
                <h3>Cómo configurar el Plugin</h3>
                <ol>
                    <li>Ir a Site administration</li>
                    <li>Ir a la sección Plugins</li>
                    <img src="https://i.postimg.cc/brBHYk8b/image6.png" alt="image6"/>
                    <li>Hacer click en export-grades-cloud</li>
                    <img src="https://i.postimg.cc/Hsr9ZZ4t/image9.png" alt="image9"/>

                </ol>
            </div>
        </div>


        <div class="col-12 mt-4" id="fill-fields">
            <h3>Cómo rellenar los campos</h3>
            <?php
            $pasos = [
                'En “Export Frequency” tienes 3 opciones para seleccionar. "Minutes", “Daily”, “Weekly” y “Monthly”. <br>
',
                'En "Minutos" se selecciona cada cuantos minutos queres que se realicen las exportaciones. <b>EJ: 1 seria cada un minuto.</b>',
                'En “Tiempo” se selecciona la hora deseada para realizar la exportación.  <b>EJ: 2 seria a las 2:00 AM.</b>',
                'Si la frecuencia de exportación es semanal, entonces en el campo “Día de la semana” se puede seleccionar el día de la semana deseado para la exportación. <b>EJ: Lunes seria cada Lunes.</b>',
                'Si la frecuencia de exportación es mensual,el campo “Día del mes” se selecciona el día del mes deseado para la exportación. <b>Ej 30 cada numero 30 del mes.</b>',
                'En "Directorio Local" se copia el path donde uno desea que se descarguen los csv. en su entorno local. <b>EJ: pathedeseado/downloads</b> si se deja vacio no se genera ningun csv de manera local.',
                'En “Google Drive Folder ID” va a ir el link del repositorio al cual desea subir las calificaciones. <br> <img src=\'https://i.postimg.cc/tTCZdR2z/Whats-App-Image-2024-06-27-at-20-47-46-4b390adc.jpg\' border=\'0\' alt=\'Whats-App-Image-2024-06-27-at-20-47-46-4b390adc\'/>',
                'Para obtener ese id, te dirigis a TU google dirve.',
                'Te posicionas en el root donde quieras generar todo el path de carpetas.',
                'Una vez posicionado dentro de la carpeta, En la URI podes selecionar el ID.<br> <img src=\'https://i.postimg.cc/JzMk7QZg/Captura-de-pantalla-1264.png\' border=\'0\' alt=\'Captura-de-pantalla-1264\'/>',
                'En “Google Drive Service Account Credentials” hay que poner las credenciales que necesitamos para poder mandar las calificaciones al drive.',
                'En “Local Directory” podemos poner el path en el cual queremos que se descargue el CSV localmente.'
            ];
            ?>
            <ol>
                <?php foreach ($pasos as $paso) { ?>
                    <li><?php echo $paso ?></li>
                <?php } ?>
            </ol>
           <img src='https://i.postimg.cc/ZKFWQy2L/Screenshot-2024-06-27-210059.png' border='0' alt='Screenshot-2024-06-27-210059'/>
        </div>
        <div class="col-12 mt-5" id="check-install">
            <h3>Cómo saber si el plugin está bien instalado</h3>
            <ol>
                <li>Ir a Site administration</li>
                <a href="https://postimages.org/" target="_blank"><img src="https://i.postimg.cc/j5RhMRZW/image3.png"
                                                                       alt="image3"/></a>
                <li>Ir a la sección “Plugins”</li>
                <a href="https://postimages.org/" target="_blank"><img src="https://i.postimg.cc/brBHYk8b/image6.png"
                                                                       alt="image6"/></a>
                <li>Hacer click en “Plugins Overview”</li>
                <a href="https://postimages.org/" target="_blank"><img src="https://i.postimg.cc/Xvf8KrcS/image1.png"
                                                                       alt="image1"/></a>
                <li>Debería aparecer en la lista de activity modules, como en el siguiente ejemplo.</li>
                <a href="https://postimages.org/" target="_blank"><img src="https://i.postimg.cc/cHTBjPbF/image4.png"
                                                                       alt="image4"/></a>
            </ol>
        </div>
        <div class="col-12 mt-4" id="faq">
            <h3>Preguntas Frecuentes</h3>
            <?php
            $faqs = [
                [
                    'q' => '¿Está el plugin disponible en la tienda de Moodle?',
                    'a' => 'Actualmente, el plugin no está disponible en la tienda de Moodle.
                    Solo se puede descargar desde el drive mencionado anteriormente.'
                ],
                [
                    'q' => 'Cuando configuro Google Drive o el directorio local,
                    ¿es la misma configuración para todos mis cursos?',
                    'a' => 'Sí, la configuración se aplica a todos los cursos.
                    Esto incluye la frecuencia de exportación, así como la configuración de Google Drive y
                     el directorio local.'
                ],
                ['q' => '¿Cómo puedo desinstalar el Plugin?', 'a' => 'ir a “Site Administration”, ir a la sección “Plugins”, luego a "Plugins overview".
                Buscar el plugin en Activity modules y le das a la opción “Uninstall”, luego borrar la carpeta “exportgrades” en tu Moodle. <br>'
                ]
            ];
            $counter = 0;
            foreach ($faqs as $faq) { ?>
                <div class="accordion accordion-flush" id="accordionFlushExample">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#flush-collapse<?php echo $counter; ?>" aria-expanded="false"
                                    aria-controls="flush-collapse<?php echo $counter; ?>">
                                <b> <?php echo $faq['q']; ?></b>
                            </button>
                        </h2>
                        <div id="flush-collapse<?php echo $counter; ?>" class="accordion-collapse collapse"
                             data-bs-parent="#accordionFlushExample">
                            <div class="accordion-body"><?php echo $faq['a']; ?></div>
                        </div>
                    </div>
                </div>
                <?php
                $counter++;
            } ?>
            <div class="accordion" id="accordionPanelsStayOpenExample">
                <p class="text-black p-0"><b>Soporte</b></p>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#panelsStayOpen-collapseOne" aria-expanded="true"
                                aria-controls="panelsStayOpen-collapseOne">
                            <b> ¿Qué debo hacer si me surge una duda? </b>
                        </button>
                    </h2>
                    <div id="panelsStayOpen-collapseOne" class="accordion-collapse collapse show">
                        <div class="accordion-body">
                            <div class="accordion-body">Nos puede contactar o enviar su consulta a este mail: <a
                                        href="mailto:grupo1ort2024@gmail.com">grupo1ort2024@gmail.com</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Load Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
<?php
echo $OUTPUT->footer();
?>