<?php

namespace App\Services;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWSProvider\JWSProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use DateTimeImmutable;

class JWTService
{
    private $jwtManager;
    private $jwtProvider;
    private $userRepository;

    public function __construct(JWTTokenManagerInterface $jwtManager, JWSProviderInterface $jwtProvider, UserRepository $userRepository)
    {
        $this->jwtManager = $jwtManager;
        $this->jwtProvider = $jwtProvider;
        $this->userRepository = $userRepository;
    }

    /**
     * @return User | Boolean - false if token is not avalaible | null is not send
     */
    public function checkToken(Request $request)
    {
        if ($request->headers->has('Authorization')) {
            $data = explode(" ", $request->headers->get('Authorization'));
            if (count($data) == 2) {
                $token = $data[1];
                try {
                    $dataToken = $this->jwtProvider->load($token);
                    if ($dataToken->isVerified($token)) {
                        $user = $this->userRepository->findOneBy(["email" => $dataToken->getPayload()["username"]]);
                        return ($user) ? $user : false;
                    }
                } catch (\Throwable $th) {
                    return false;
                }
            }
        } else {
            return true;
        }
        return false;
    }

    public function sendJsonErrorToken($nullToken): array
    {
        return [
            'error' => true,
            'message' => ($nullToken) ? "Authentification requise. Vous devez être connecté pour effectuer cette action." : "Vous n'êtes pas autorisé à accéder aux informations de cet artiste.",
        ];
    }

    /**
     * Génère un token JWT.
     *
     * @param array $header    En-tête du token.
     * @param array $payload   Charge utile du token.
     * @param string $secret   Clé secrète pour la signature.
     * @param int $validity    Durée de validité du token en secondes (par défaut 3 heures).
     * @return string          Token JWT généré.
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        // Ajoute la date de création (iat) et la date d'expiration (exp) à la charge utile
        if ($validity > 0) {
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;

            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }

        // Encode l'en-tête et la charge utile en JSON, puis en base64
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        // Nettoie les valeurs encodées (remplace les caractères +, / et = par -, _ et '')
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        // Encode la clé secrète en base64 pour la signature HMAC
        $secret = base64_encode($secret);

        // Génère la signature HMAC-SHA256
        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);

        // Nettoie la signature encodée (remplace les caractères +, / et = par -, _ et '')
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        // Crée le token JWT en combinant l'en-tête, la charge utile et la signature
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }

    /**
     * Vérifie si un token JWT est correctement formé.
     *
     * @param string $token    Token JWT à vérifier.
     * @return bool            Vrai si le token est correctement formé, sinon faux.
     */
    public function isValid(string $token): bool
    {
        // Vérifie si le token correspond à la structure typique d'un token JWT
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    /**
     * Récupère la charge utile (payload) d'un token JWT.
     *
     * @param string $token    Token JWT.
     * @return array           Charge utile (payload) du token.
     */
    public function getPayload(string $token): array
    {
        // Divise le token en parties séparées par des points (.)
        $array = explode('.', $token);

        // Décode la charge utile en JSON et la renvoie sous forme de tableau associatif
        return json_decode(base64_decode($array[1]), true);
    }

    /**
     * Récupère l'en-tête (header) d'un token JWT.
     *
     * @param string $token    Token JWT.
     * @return array           En-tête (header) du token.
     */
    public function getHeader(string $token): array
    {
        // Divise le token en parties séparées par des points (.)
        $array = explode('.', $token);

        // Décode l'en-tête en JSON et la renvoie sous forme de tableau associatif
        return json_decode(base64_decode($array[0]), true);
    }

    /**
     * Vérifie si un token JWT a expiré.
     *
     * @param string $token    Token JWT.
     * @return bool            Vrai si le token a expiré, sinon faux.
     */
    public function isExpired(string $token): bool
    {
        // Récupère la charge utile (payload) du token
        $payload = $this->getPayload($token);

        // Récupère l'heure actuelle
        $now = new DateTimeImmutable();

        // Compare la date d'expiration (exp) avec l'heure actuelle
        return $payload['exp'] < $now->getTimestamp();
    }

    /**
     * Vérifie la validité d'un token JWT.
     *
     * @param string $token    Token JWT.
     * @param string $secret   Clé secrète utilisée pour la signature.
     * @return bool            Vrai si le token est valide, sinon faux.
     */
    public function check(string $token, string $secret): bool
    {
        // Récupère l'en-tête et la charge utile du token
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        // Régénère un token en utilisant les mêmes données d'en-tête et de charge utile
        $verifToken = $this->generate($header, $payload, $secret, 0);

        // Vérifie si le token régénéré correspond au token d'origine
        return $token === $verifToken;
    }
}
