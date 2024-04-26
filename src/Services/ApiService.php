<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class ApiService
{
    private array $allowedFields;
    private array $requiredFields;
    /**
     * Vérifie si les données fournies sont valides et complètes pour un type spécifique.
     * 
     * @param string $type - Le type de données à valider (par exemple 'user').
     * @param array $data - Les données fournies à valider.
     * @return array - Un tableau contenant un indicateur de validation et les champs manquants/invalides.
     */
    public function checkValidData(string $type, array $data): array
    {
        // Vérifie si le type est autorisé
        if (!isset($this->allowedFields[$type])) {
            return [
                'yes' => false,
                'missingFields' => [],
                'invalidFields' => array_keys($data)
            ];
        }

        $allowedFields = $this->allowedFields[$type]['body'];

        // Vérifie si des champs requis sont manquants
        $missingFields = array_diff($allowedFields, array_keys($data));

        // Vérifie si des champs non autorisés sont présents
        $invalidFields = array_diff(array_keys($data), $allowedFields);

        // Vérifie si toutes les conditions sont satisfaites
        $isValid = empty($missingFields) && empty($invalidFields);

        return [
            'yes' => $isValid,
            'missingFields' => $missingFields,
            'invalidFields' => $invalidFields
        ];
    }



    /**
     * Check if all the required fields are present in the request
     * 
     * @param Request $request - the request object
     * @param array $requiredFields - the required fields
     */
    public function hasRequiredBodyKeys(Request $request, array $requiredFields): array
    {
        $bodyData = json_decode($request->getContent(), true);
        $missingKeys = [];

        foreach ($requiredFields as $requiredField) {
            if (!array_key_exists($requiredField, $bodyData)) {
                $missingKeys[] = $requiredField;
            }
        }



        return [
            'yes' => count($missingKeys) === 0,
            'missingKeys' => $missingKeys
        ];
    }


    /**
     * Check if the request has only valid fields
     * @param Request $request - the request object
     * @param array $allowedProperties - the allowed properties
     * @return array
     */
    public function hasOnlyValidBodyKeys(Request $request, array $allowedProperties): array
    {
        $bodyData = json_decode($request->getContent(), true);

        if (count($allowedProperties) === 0) {
            if (!$bodyData) {
                return [
                    'yes' => true,
                    'invalidKeys' => []
                ];
            } else {
                return [
                    'yes' => false,
                    'invalidKeys' => []
                ];
            }
        }

        $invalidBodyKeys = array_diff(array_keys($bodyData), $allowedProperties);

        return [
            'yes' => count($invalidBodyKeys) === 0,
            'invalidKeys' => $invalidBodyKeys
        ];
    }

    /**
     * Check if the request has a valid body
     * @param Request $request - the request object
     * @param array $allowedProperties - the allowed properties
     * @param array $requiredFields - the required fields
     * @param bool $isStrictRequired - if false, the required fields are not required for edit
     * @return array
     */
    public function hasValidBody(Request $request, array $allowedProperties, array $requiredFields, bool $isStrictRequired = true): array
    {
        $hasOnlyValidBodyKeys = $this->hasOnlyValidBodyKeys($request, $allowedProperties);
        $hasRequiredBodyKeys = $this->hasRequiredBodyKeys($request, $requiredFields);


        // When editing, the required fields are not required
        if ($request->getMethod() === 'PUT' && !$isStrictRequired) {
            return [
                'yes' => $hasOnlyValidBodyKeys['yes'],
                'invalidKeys' => $hasOnlyValidBodyKeys['invalidKeys'],
                'missingKeys' => []
            ];
        }


        return [
            'yes' => $hasOnlyValidBodyKeys['yes'] && $hasRequiredBodyKeys['yes'],
            'invalidKeys' => $hasOnlyValidBodyKeys['invalidKeys'],
            'missingKeys' => $hasRequiredBodyKeys['missingKeys']
        ];
    }


    /**
     * Check if the request has only valid query parameters
     * @param Request $request - the request object
     * @param array $allowedProperties - the allowed properties
     * @return array
     */
    public function hasOnlyValidQueryParameters(Request $request, array $allowedProperties = []): array

    {
        $queryParameters = $request->query->all();


        if (count($allowedProperties) === 0) {
            if (count($queryParameters) === 0) {
                return [
                    'yes' => true,
                    'invalidParams' => []
                ];
            } else {
                return [
                    'yes' => false,
                    'invalidParams' => []
                ];
            }
        }


        $invalidQueryParameters = array_diff(array_keys($queryParameters), $allowedProperties);



        return [
            'yes' => count($invalidQueryParameters) === 0,
            'invalidParams' => $invalidQueryParameters
        ];
    }

    /**
     * Check if the request has all required query param
     * @param Request $request - the request object
     * @param array $requiredProperties - the required properties
     * @return array
     */
    public function hasRequiredQueryParameters(Request $request, array $requiredProperties): array
    {
        $queryParameters = $request->query->all();
        $missingQueryParameters = [];


        foreach ($requiredProperties as $requiredProperty) {
            if (!array_key_exists($requiredProperty, $queryParameters)) {
                $missingQueryParameters[] = $requiredProperty;
            }
        }



        return [
            'yes' => count($missingQueryParameters) === 0,
            'missingParams' => $missingQueryParameters
        ];
    }

    /**
     * Check if the request has valid query parameters
     * @param Request $request - the request object
     * @param array $allowedProperties - the allowed properties
     * @param array $requiredProperties - the required properties
     * @return array
     */
    public function hasValidQueryParameters(Request $request, array $allowedProperties, array $requiredProperties): array
    {
        $hasOnlyValidQueryParameters = $this->hasOnlyValidQueryParameters($request, $allowedProperties);
        $hasRequiredQueryParameters = $this->hasRequiredQueryParameters($request, $requiredProperties,);

        // echo "=======================: \n";
        // echo "hasOnlyValidQueryParameters: \n";
        // print_r($hasOnlyValidQueryParameters);
        // echo "hasRequiredQueryParameters: \n";
        // print_r($hasRequiredQueryParameters);
        // echo "=======================: \n";

        return [
            'yes' => $hasOnlyValidQueryParameters['yes'] && $hasRequiredQueryParameters['yes'],
            'invalidParams' => $hasOnlyValidQueryParameters['invalidParams'],
            'missingParams' => $hasRequiredQueryParameters['missingParams']
        ];
    }

    /**
     * Check if a request has a valid body and query parameters
     * 
     * Expressively describe the validation errors if any validation failed
     * 
     * @param Request $request - the request object
     * @param array $allowedBodyProperties - the allowed body properties
     * @param array $requiredBodyProperties - the required body properties
     * @param array $allowedQueryProperties - the allowed query properties
     * @param array $requiredQueryProperties - the required query properties
     * @return array
     */
    public function hasValidBodyAndQueryParameters(
        Request $request,
        array $allowedBodyProperties,
        array $requiredBodyProperties,
        array $allowedQueryProperties,
        array $requiredQueryProperties,
        bool $isStrictRequired = true
    ): array {

        $hasValidBody = $this->hasValidBody($request, $allowedBodyProperties, $requiredBodyProperties, $isStrictRequired);
        $hasValidQueryParameters = $this->hasValidQueryParameters($request, $allowedQueryProperties, $requiredQueryProperties);


        return [
            'yes' => $hasValidBody['yes'] && $hasValidQueryParameters['yes'],
            'body' => [
                'invalidKeys' => $hasValidBody['invalidKeys'],
                'missingKeys' => $hasValidBody['missingKeys']
            ],
            'params' => [
                'invalidParams' => $hasValidQueryParameters['invalidParams'],
                'missingParams' => $hasValidQueryParameters['missingParams']
            ]
        ];
    }

    /**
     * Check if the request has only valid body keys
     * 
     * @param $length - the length of the random string to generate
     */
    public function generateRandomString($length = 6): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    /**
     * Check if the given string is a valid password:
     * 
     */
    public function isValidPassword(string $password): bool
    {
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $digit = preg_match('@[0-9]@', $password);
        $symbol = preg_match('@[^\w]@', $password);

        return $uppercase && $lowercase && $digit && $symbol && strlen($password) >= 6;
    }
}
