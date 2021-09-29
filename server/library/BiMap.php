<?php

namespace Library;
// author: Psyrus, source: https://stackoverflow.com/questions/15794858/php-bi-directional-map
class BiMap
{
    private $KtoV, $VtoK;

    public function __constructor()
    {
        $this->KtoV = []; // for version < 5.4.0, syntax must be: $this->KtoV = array();
        $this->VtoK = [];
    }

    public function getKey($v)
    {
        if ($this->hasValue($v)) {
            return $this->VtoK[$v];
        } else {
            return null;
        }
    }

    public function getAllKeys()
    {
        if ($this->KtoV) {
            return array_keys($this->KtoV);
        } else {
            return $this->KtoV;
        }
    }

    public function getValue($k)
    {
        if ($this->hasKey($k)) {
            return $this->KtoV[$k];
        } else {
            return null;
        }
    }

    public function getAllValues()
    {
        if ($this->VtoK) {
            return array_keys($this->VtoK);
        } else {
            return $this->VtoK;
        }
    }

    public function hasKey($k)
    {
        return isset($this->KtoV[$k]);
    }

    public function hasValue($v)
    {
        return isset($this->VtoK[$v]);
    }

    public function put($k, $v)
    {
        if ($this->hasKey($k)) {
            $this->removeKey($k);
        }
        if ($this->hasValue($v)) {
            $this->removeValue($v);
        }
        $this->KtoV[$k] = $v;
        $this->VtoK[$v] = $k;
    }

    public function putAll($array)
    {
        foreach ($array as $k => $v) {
            $this->put($k, $v);
        }
    }

    public function removeKey($k)
    {
        if ($this->hasKey($k)) {
            unset($this->VtoK[$this->KtoV[$k]]);
            $v = $this->KtoV[$k];
            unset($this->KtoV[$k]);
            return $v;
        } else {
            return null;
        }
    }

    public function removeValue($v)
    {
        if ($this->hasValue($v)) {
            unset($this->KtoV[$this->VtoK[$v]]);
            $k = $this->VtoK[$v];
            unset($this->VtoK[$v]);
            return $k;
        } else {
            return null;
        }
    }
}
