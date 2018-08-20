<?php

trait manager
{

    /**
     * @param $file
     * @param $ligne
     * @param string $message
     * @throws Exception
     */
    protected function throw_exception($file, $ligne, $message = '') {
        throw new Exception($message.';'.$ligne.';'.$file);
    }

    /**
     * @param $champ
     * @return mixed
     * @throws Exception
     */
    protected function get($champ) {
        if(isset($this->$champ) || $this->$champ === null || empty($this->$champ)) return $this->$champ;
        else $this->throw_exception(__FILE__, __LINE__, 'Le champ '.$champ.' n\'existe pas');
        return null;
    }

    /**
     * @param $champ
     * @param $value
     * @return $this
     * @throws Exception
     */
    protected function set($champ, $value) {
        if(isset($this->$champ) || $this->$champ === null) $this->$champ = $value;
        else $this->throw_exception(__FILE__, __LINE__, 'Le champ '.$champ.' n\'existe pas');
        return $this;
    }

    /**
     * @param $champ
     * @param $key
     * @return mixed
     * @throws Exception
     */
    protected function get_array($champ, $key) {
        $array = $this->get($champ);
        if(isset($array[$key]) || empty($array[$key])) {
            return $array[$key];
        }
        $this->throw_exception(__FILE__, __LINE__, 'La clé '.$key.' du tableau '.$champ.' n\'existe pas');
        return null;
    }

    /**
     * @param $champ
     * @param mixed $key
     * @param mixed $value
     * @return $this
     * @throws Exception
     */
    protected function set_array($champ, $key = null, $value = null) {
        if(isset($this->$champ) || empty($this->$champ)) {
            if($key === null) {
                $this->$champ[] = $value;
            }
            else {
                $this->$champ[$key] = $value;
            }
        }
        else $this->throw_exception(__FILE__, __LINE__, 'Le champ '.$champ.' n\'existe pas');
        return $this;
    }
}