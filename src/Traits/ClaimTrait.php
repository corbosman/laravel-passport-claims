<?php

namespace CorBosman\Passport\Traits;

trait ClaimTrait
{
    protected $claims = [];

    /**
     * add a claim for this token
     *
     * @param $key
     * @param $value
     */
    public function addClaim($key, $value)
    {
        $this->claims[$key] = $value;
    }

    /**
     * return all claims
     *
     * @return array
     */
    public function claims()
    {
        return $this->claims;
    }

}
