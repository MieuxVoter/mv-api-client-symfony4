<?php


namespace App\Adapter;


use MjOpenApi\ApiException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Validator\ViolationMapper\ViolationMapper;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;


class ApiExceptionAdapter
{

    /** @var ContainerInterface */
    protected $container;

    /**
     * CreateBallotController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    ///
    ///

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
            $output = $body;
        } else {
            if (isset($data['hydra:title'])) {
                $output .= empty($output) ? '' : "  \n";
                $output .= $data['hydra:title'] . "!";
            }
            if (isset($data['hydra:description'])) {
                $output .= empty($output) ? '' : "  \n";
                $output .= $data['hydra:description'];
            }
        }

        if (empty($output)) {
            $output = $exception->getMessage();
        }

        if ($html) {
            $output = str_replace("\n", "<br />", $output);
        }

        return $output;
    }

    public function toHtml(ApiException $exception): string
    {
        $output = "";
        $body = $exception->getResponseBody();
        $jsonData = null;
        try {
            $jsonData = json_decode($body, true);
        } catch (\Exception $e) {}

        if (null === $jsonData) {
            $output = $body;
        } else {
            $output = dump($jsonData);
//            if (isset($jsonData['hydra:title'])) {
//                $output .= empty($output) ? '' : "  \n";
//                $output .= $jsonData['hydra:title'] . "!";
//            }
//            if (isset($jsonData['hydra:description'])) {
//                $output .= empty($output) ? '' : "  \n";
//                $output .= $jsonData['hydra:description'];
//            }
        }

        if (empty($output)) {
            $output = $exception->getMessage();
        }

        return $output;
    }



    public function respond(ApiException $exception, Response $response)
    {
//        dump($exception);
//        dd($response);
        $message = $this->toString($exception);
        $this->addFlash("error", $message);
        return $response;
    }



    /**
     * Adds a flash message to the current session for type.
     *
     * @throws \LogicException
     *
     * @final
     */
    protected function addFlash(string $type, $message)
    {
        if (!$this->container->has('session')) {
            throw new \LogicException('You can not use the addFlash method if sessions are disabled. Enable them in "config/packages/framework.yaml".');
        }

        $this->container->get('session')->getFlashBag()->add($type, $message);
    }
}