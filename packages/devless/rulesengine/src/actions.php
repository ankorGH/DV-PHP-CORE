<?php

    namespace Devless\RulesEngine;

    use App\Helpers\Helper;
    use App\Helpers\ActionClass;

    trait actions
    {
        /**
         * check if on intended table.
         *
         * @param string $expectedTableName
         *
         * @return mixed|string
         */
        public function onTable()
        {
            $expectedTableNames = func_get_args();
            if (!$this->execOrNot) {
                return $this;
            }
           
            $this->tableName = (is_array($this->tableName)) ? $this->tableName[0] : $this->tableName;
            $this->execOrNot = (in_array($this->tableName , $expectedTableNames));

            return $this;
        }

        /**
         * Stop execcution with an exception.
         *
         * @param null $msg
         *
         * @return mixed|string
         */
        public function succeedWith($msg = null)
        {
            if (!$this->execOrNot) {
                return $this;
            }

            $msg  = (is_array($msg))? json_encode($msg):$msg;
            Helper::interrupt(1000, $msg);
            return $this;
            
        }

        /**
         * Stop execution with an exception.
         *
         * @param null $msg
         *
         * @return mixed|string
         */
        public function failWith($msg = null)
        {
            if (!$this->execOrNot) {
                return $this;
            }
            $msg  = (is_array($msg))? json_encode($msg):$msg;
            Helper::interrupt(1001, $msg);
            return $this;
        }
        /**
         * Call on an ActionClass.
         *
         * @param  $service
         * @param  $method
         * @param null $params
         *
         * @return mixed|string
         */
        public function run($service, $method, $params = null, $remoteUrl = null, $token = null)
        {
            if (!$this->execOrNot) {
                return $this;
            }
            
            $params = ($params) ? $params : [];
            if ($remoteUrl && $token) {
                $this->results = ActionClass::remoteExecute($service, $method, $params, $remoteUrl, $token);
            } else {
                $this->results = ActionClass::execute($service, $method, $params);
            }
            $this->answered = true;

            return $this;

        }

        /**
         * Make remote requests
         *
         * @param  STRING $method
         * @param  STRING $url
         * @param  JSON $data
         *
         * @return $this
         */
        public function makeRemoteRequest($method, $url, $data='{}', $headers=[])
        {
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => strtoupper($method),
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => $headers,
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
              $this->results = $err;
            } else {
                $this->results =  json_decode($response, true);
            }
            return $this;
        }
        /**
         * Get results variable and set to variable.
         *
         * @param $input_var
         *
         * @return $this
         */
        public function getRunResult(&$input_var)
        {
            if (!$this->execOrNot) {
                return $this;
            }
            $this->to($input_var);
            return $this;
        }

        /**
         * Get results variable and set to variable.
         *
         * @param $input_var
         *
         * @return $this
         */
        public function storeAs(&$input_var)
        {
            $this->getRunResult($input_var);
            return $this;
        }

        /**
         * Assign one input to another 
         *
         * @param $input_var
         *
         * @return $this
         */
        public function assign($input)
        {
            if (!$this->execOrNot) {
                return $this;
            }
            $this->results = $input;    
            return $this;
        }


        /**
         * Get results variable and set to variable.
         *
         * @param $output
         *
         * @return $this
         */
        public function to(&$output)
        {
            if (!$this->execOrNot) {
                return $this;
            }
            $output = $this->results;

            return $this;
        }

        /**
        * Get results variable and set to variable.
         *
         * @param $output
         *
         * @return $this
         */
        public function from($output)
        {
            $this->assign($output);
            return $this;
        }

        /**
         * Assign $input to $output 
         *
         * @param $input
         * @param $output  
         *
         * @return $this
         */
        public function assignValues(&$input, &$output)
        {
            $output = $input;    
            return $this;
        }


    }