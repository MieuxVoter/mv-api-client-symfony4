<?php


namespace App\Adapter;


use MjOpenApi\ApiException;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\ConstraintViolation;


class ApiExceptionAdapter
{
    /**
     * @param FormInterface $form
     * @param ApiException $exception
     */
    public function setFormErrorsIfAny(FormInterface $form, ApiException $exception)
    {
        $body = $exception->getResponseBody();
        $data = null;
        try {
            $data = json_decode($body, true);
        } catch (\Exception $e) {
        }
        if (null === $data) {
            return;
        }

//        dd($data);

        if (isset($data['@type'])) {
            switch ($data['@type']) {
                case 'ConstraintViolationList':
                    if (isset($data['violations'])) {
                        foreach ($data['violations'] as $violation) {
                            $vm = new ViolationMapper();

                            // Format should be: children[businessAddress].children[postalCode]
                            $violation['propertyPath'] = 'children[' . str_replace('.', '].children[', $violation['propertyPath']) . ']';

                            // Convert error to violation.
                            $constraint = new ConstraintViolation(
                                $violation['message'],
                                $violation['message'],
                                array(),
                                '',
                                $violation['propertyPath'],
                                null
                            );

                            $vm->mapViolation($constraint, $form);
                        }

                    }
                    break;
            }
        }
    }

    public function toString(ApiException $exception, $html = false): string
    {
        $output = "";

        $body = $exception->getResponseBody();
        $data = null;
        try {
            $data = json_decode($body, true);
        } catch (\Exception $e) {
        }

        if (null === $data) {
            return $body;
        }

        if (isset($data['hydra:title'])) {
            $output .= empty($output) ? '' : "  \n";
            $output .= $data['hydra:title'] . "!";
        }
        if (isset($data['hydra:description'])) {
            $output .= empty($output) ? '' : "  \n";
            $output .= $data['hydra:description'];
        }

        if (empty($output)) {
            $output = $exception->getMessage();
        }

        if ($html) {
            $output = str_replace("\n", "<br />", $output);
        }

        return $output;
    }
}