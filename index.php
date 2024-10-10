<?php
    /*
     * Copyright 2014 Hook Global, LLC
     *
     * Licensed under the Apache License, Version 2.0 (the "License");
     * you may not use this file except in compliance with the License.
     * You may obtain a copy of the License at
     *
     *   http://www.apache.org/licenses/LICENSE-2.0
     *
     * Unless required by applicable law or agreed to in writing, software
     * distributed under the License is distributed on an "AS IS" BASIS,
     * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
     * See the License for the specific language governing permissions and
     * limitations under the License.
     */

    require('lib/zoho.php');

    // Cargar config.json y verificar si es válido
    $config = json_decode(file_get_contents("config.json"), true);
    if ($config && isset($config['organizations']) && is_array($config['organizations'])) {
        $organizations = $config['organizations'];
    } else {
        $organizations = []; // Si hay un problema con el config, se usa un array vacío
    }
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Zoho Check Print Plugin</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS for the 'Heroic Features' Template -->
    <link href="css/bootstrap-theme.min.css" rel="stylesheet">

    <!-- JavaScript -->
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-fixed-top navbar-inverse" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.php"><img style="margin-top:-7px;"
                        src="http://hookglobal.com/public/img/logo.png" /></a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="jumbotron hero-spacer">
            <h2>
                <img src="http://2j9zen46cyp13k47i01s551mmxd.wpengine.netdna-cdn.com/wp-content/uploads/2013/07/zoho-logo-200x105.png"
                    style="width:100px;margin-bottom:7px;" />
                <b>Check Print</b>
            </h2>
            <p>
                Bienvenido al plugin Zoho Books Check Print! Para configurar el plugin, usa el archivo config.json
                proporcionado en
                el directorio raíz. Debes especificar tu API de Zoho Books y al menos una organización para comenzar a
                usar el
                plugin. Este es un software gratuito proporcionado bajo la
                <a href="http://www.apache.org/licenses/LICENSE-2.0.html">
                    Licencia Apache 2.0
                </a>.
                <br />
            <p>
                <a class="btn btn-default" href="https://github.com/hookglobal/zoho-check-print">GitHub</a>
                <a class="btn btn-success" href="https://books.zoho.com">Zoho Books</a>
            </p>
            </p>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h3>Organizaciones</h3>
            </div>
        </div>

        <div class="row text-center">
            <?php
                    // Recorrer las organizaciones si están definidas
                    foreach ($organizations as $key => $value) {
                ?>
            <div class="col-lg-3 col-md-6 hero-feature">
                <div class="thumbnail">
                    <div class="caption">
                        <h3><?php echo htmlspecialchars($key); ?></h3>
                        <h5>ID de la Organización: <?php echo htmlspecialchars($value); ?></h5>
                        <p>
                            <button class="btn btn-primary" data-toggle="modal"
                                data-target="#modal<?php echo htmlspecialchars($value); ?>">Imprimir Cheque</button>
                        </p>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>

        <hr>

        <footer>
            <div class="row">
                <div class="col-lg-12">
                    <p>Creado por <a href="http://hookglobal.com/">Hook Global</a></p>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modales para imprimir -->
    <?php foreach ($organizations as $key => $value) { ?>
    <div class="modal fade" id="modal<?php echo htmlspecialchars($value); ?>" tabindex="-1" role="dialog"
        aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span><span class="sr-only">Cerrar</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">Imprimir Cheque para <?php echo htmlspecialchars($key); ?>
                    </h4>
                </div>

                <form action="lib/print.php" method="post">
                    <div class="modal-body">
                        <h4>Monto</h4>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-usd"></span></span>
                            <input type="text" class="form-control"
                                name="amount<?php echo htmlspecialchars($value); ?>">
                        </div><br />

                        <h4>Beneficiario</h4>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-user"></span></span>
                            <input type="text" class="form-control" name="payee<?php echo htmlspecialchars($value); ?>">
                        </div><br />

                        <h4>Memo</h4>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-comment"></span></span>
                            <input type="text" class="form-control" name="memo<?php echo htmlspecialchars($value); ?>">
                        </div><br />

                        <?php
    // Cargar las plantillas desde la carpeta /templates/
    $templates = array();
    foreach (glob("templates/*.json") as $templateFile) {
        $templateContent = json_decode(file_get_contents($templateFile), true);
        if (isset($templateContent['template']['name'])) {
            // Si existe el campo 'template' y 'name', lo agregamos a la lista
            array_push($templates, $templateContent);
        }
    }
?>

                        <h4>Plantilla de Impresión</h4>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-print"></span></span>
                            <select class="form-control" name="template<?php echo htmlspecialchars($value); ?>">
                                <?php foreach ($templates as $template) { ?>
                                <option value="<?php echo htmlspecialchars($template['template']['name']); ?>">
                                    <?php echo htmlspecialchars($template['template']['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div><br />

                        <input type="hidden" name="organization_id" value="<?php echo htmlspecialchars($value); ?>" />

                        Log Expense to <img style="width:100px;"
                            src="http://blogs.zoho.com/image/13000001065148/zoho-books-logo.png" />
                        &nbsp;<input type="checkbox" name="expense<?php echo htmlspecialchars($value); ?>"
                            id="expense<?php echo htmlspecialchars($value); ?>" /><br />

                            <script>
    $("#expense<?php echo htmlspecialchars($value); ?>").change(function() {
        var bank = $("#bankacc<?php echo htmlspecialchars($value); ?>");
        var expense = $("#expenseacc<?php echo htmlspecialchars($value); ?>");

        // Habilitar solo si hay cuentas disponibles
        if(bank.find('option').length > 1) {
            bank.prop('disabled', false);
        } else {
            alert('No se encontraron cuentas bancarias disponibles.');
        }

        if(expense.find('option').length > 1) {
            expense.prop('disabled', false);
        } else {
            alert('No se encontraron cuentas de gastos disponibles.');
        }
    });
</script>


                        <h4>Cuenta Bancaria/Cash</h4>
                        <div class="input-group">
                            <span class="input-group-addon"><span class="glyphicon glyphicon-credit-card"></span></span>
                            <select class="form-control" name="bankacc<?php echo htmlspecialchars($value); ?>"
                                id="bankacc<?php echo htmlspecialchars($value); ?>" disabled>
                                <?php if (!empty($accounts)) {
            foreach ($accounts['bankaccounts'] as $account) { ?>
                                <option value="<?php echo htmlspecialchars($account['account_id']); ?>">
                                    <?php echo htmlspecialchars($account['account_name']); ?> -
                                    $<?php echo htmlspecialchars($account['balance']); ?>
                                    (<?php echo htmlspecialchars($account['account_type']); ?>)
                                </option>
                                <?php }
        } else {
            echo "<option>No se pudieron obtener las cuentas bancarias. Revisa la respuesta de Zoho.</option>";
        } ?>
                            </select>
                        </div><br />

                        <h4>Cuenta de Gastos</h4>
                        <div class="input-group">
                            <span class="input-group-addon"><span
                                    class="glyphicon glyphicon-shopping-cart"></span></span>
                            <select class="form-control" name="expenseacc<?php echo htmlspecialchars($value); ?>"
                                id="expenseacc<?php echo htmlspecialchars($value); ?>" disabled>
                                <?php if (!empty($expenseAccounts)) {
            foreach ($expenseAccounts['chartofaccounts'] as $account) { ?>
                                <option value="<?php echo htmlspecialchars($account['account_id']); ?>">
                                    <?php echo htmlspecialchars($account['account_name']); ?>
                                </option>
                                <?php }
        } else {
            echo "<option>No se pudieron obtener las cuentas de gastos. Revisa la respuesta de Zoho.</option>";
        } ?>
                            </select>
                        </div><br />


                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Imprimir Cheque</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php } ?>
</body>

</html>