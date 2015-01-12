<?php
/**
 * Fol\Http\RequestParameters
 *
 * Class to store request variables
 */
namespace Fol\Http;

class RequestParameters implements \ArrayAccess
{
    use ContainerTrait { get as private parentGet; }

    /**
     * Gets one or all parameters. You can gets the subvalues using brackets:
     *
     * $input->get('user') Returns, for example: array('name' => 'xan', 'age' => 34)
     * $input->get('user[age]') Returns 34
     *
     * @param string $name The parameter name
     *
     * @return mixed
     */
    public function get($name = null)
    {
        if (is_string($name) && (strpos($name, '[') !== false) && (strpos($name, ']') !== false)) {
            $subarrays = explode('[', str_replace(']', '', $name));
            $value = $this->items;

            while ($subarrays) {
                $value = $value[array_shift($subarrays)];
            }

            if (isset($value)) {
                return $value;
            }
        }

        return $this->parentGet($name);
    }
}
