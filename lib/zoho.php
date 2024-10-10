<?php
    /*
     * Copyright 2013 Cart Designers, LLC
     *
     * Original Author: Ransom Carroll [github.com/ransomcarroll]
     * Modified by Hook Global, LLC for Zoho Check Print Plugin
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

    define("METHOD_POST", 1);
    define("METHOD_PUT", 2);
    define("METHOD_GET", 3);

    class ZohoBooks {
        private $timeout = 10;
        private $debug = false;
        private $advDebug = false; // Note that enabling advanced debug will include debugging information in the response possibly breaking up your code
        private $zohoBooksApiVersion = "3";
        public $responseCode;

        private $endPointUrl;
        private $apiKey;
        private $expensesUrl;
        private $bankAccountsUrl;
        private $chartOfAccountsUrl;

        public function __construct($organization_id, $api_token){
            $this->apiKey = $api_token;
            $this->organizationId = $organization_id;

            $this->endPointUrl               = "https://www.zohoapis.com/books/v3/";
            $this->expensesUrl               = $this->endPointUrl."expenses";
            $this->bankAccountsUrl           = $this->endPointUrl."bankaccounts";
            $this->chartOfAccountsUrl        = $this->endPointUrl."chartofaccounts";
        }

        public function createExpense($data) {
            $url = $this->expensesUrl;
            $curl = curl_init($url);

            curl_setopt_array($curl, array(
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_RETURNTRANSFER => true
            ));

            $result = curl_exec($curl);

            return json_decode($result, true);
        }

        public function getChartofAccounts($filter = "AccountType.Expense", $sort = "account_name") {
            $params = array(
                "filter_by" => $filter,
                "sort_column" => $sort
            );
        
            $call = $this->callZohoBooks($this->chartOfAccountsUrl, null, METHOD_GET, 1, $params);
        
            // Imprimir la respuesta para depuración
            echo "<pre>Respuesta de Zoho (Cuentas de Gastos): ";
            print_r($call);
            echo "</pre>";
        
            // Si la conexión es exitosa
            if ($this->responseCode == 200) {
                return $call;
            } else {
                echo "Error al obtener las cuentas de gastos: HTTP Status " . $this->responseCode;
                return null;
            }
        }

        public function getBankAccounts($filter = "Status.Active", $sort = "account_name") {
            $params = array(
                "filter_by" => $filter,
                "sort_column" => $sort
            );
        
            $call = $this->callZohoBooks($this->bankAccountsUrl, null, METHOD_GET, 1, $params);
        
            // Imprimir la respuesta para depuración
            echo "<pre>Respuesta de Zoho (Cuentas Bancarias): ";
            print_r($call);
            echo "</pre>";
        
            // Si la conexión es exitosa
            if ($this->responseCode == 200) {
                return $call;
            } else {
                echo "Error al obtener las cuentas bancarias: HTTP Status " . $this->responseCode;
                return null;
            }
        }
        /*
         * This function communicates with Zoho Books REST API.
         * You don't need to call this function directly. It's only for inner class working.
         *
         * @param string $url
         * @param string $data Must be a json string
         * @param int $method See constants defined at the beginning of the class
         * @param int $page equates to page number for paginating
         * @return string JSON or null
         */
        private function callZohoBooks($url, $data = null, $method = METHOD_GET, $page = 1, $params){
            $curl = curl_init();
            $filter = '';
            if (!empty($params)) {
                foreach ($params as $key => $value) {
                    $filter .= '&' . $key . '=' . $value;
                }
            }
            curl_setopt($curl, CURLOPT_URL, $url . '?authtoken=' . $this->apiKey . '&organization_id=' . $this->organizationId . '&page=' . $page . $filter);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_FAILONERROR, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
            
            // Habilitar la depuración avanzada si está activada
            if ($this->advDebug) {
                curl_setopt($curl, CURLOPT_HEADER, true);
                curl_setopt($curl, CURLOPT_VERBOSE, true);
            }
        
            // Ejecutar la solicitud cURL
            try {
                $return = curl_exec($curl);
                $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                
                // Registrar en el log del servidor
                error_log("HTTP Response Code: " . $this->responseCode);
                error_log("Respuesta de Zoho: " . $return);
        
                // Comprobar si hubo algún error en la respuesta
                if (curl_errno($curl)) {
                    error_log("Error en cURL: " . curl_error($curl));
                }
        
                if ($this->responseCode >= 400) {  // Manejar errores de HTTP
                    echo "Error: HTTP Status Code " . $this->responseCode;
                } else if ($this->responseCode == 200) {
                    echo "Conexión exitosa a Zoho Books!";
                }
        
            } catch (Exception $ex) {
                error_log("Error en cURL: " . $ex->getMessage());
                echo "Error en cURL: " . $ex->getMessage();
                $return = null;
            }
        
            curl_close($curl);
        
            return $return;
        }
        
        
    }
?>