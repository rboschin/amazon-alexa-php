<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Validation;

use GuzzleHttp\Client;
use Rboschin\AmazonAlexa\Exception\OutdatedCertExceptionException;
use Rboschin\AmazonAlexa\Exception\RequestInvalidSignatureException;
use Rboschin\AmazonAlexa\Exception\RequestInvalidTimestampException;
use Rboschin\AmazonAlexa\Request\Request;
use Psr\Http\Client\ClientInterface as Psr18ClientInterface;

/**
 * This is a validator for amazon echo requests. It validates the timestamp of the request and the request signature.
 * 
 * Features:
 * - PSR-18 HTTP client support
 * - Configurable certificate cache directory
 * - Option to disable signature validation (development only)
 * - Dedicated certificate validation via CertValidator
 */
class RequestValidator
{
    /**
     * Basic value for timestamp validation. 150 seconds is suggested by amazon.
     */
    public const TIMESTAMP_VALID_TOLERANCE_SECONDS = 150;

    private CertValidator $certValidator;

    /**
     * @param int $timestampTolerance Timestamp tolerance in seconds
     * @param ClientInterface|Client|null $client HTTP client for fetching certificates (PSR-18 or Guzzle)
     * @param string|null $certCacheDir Directory for certificate cache
     * @param bool $disableSignatureValidation Disable signature validation (dev/test only)
     */
    public function __construct(
        protected int $timestampTolerance = self::TIMESTAMP_VALID_TOLERANCE_SECONDS,
        protected $client = null,
        ?string $certCacheDir = null,
        protected bool $disableSignatureValidation = false,
    ) {
        $this->certValidator = new CertValidator($certCacheDir);
        
        // Default to Guzzle client if no client provided
        if ($this->client === null) {
            $this->client = new Client();
        }
    }

    /**
     * Validate request data.
     *
     * @throws OutdatedCertExceptionException
     * @throws RequestInvalidSignatureException
     * @throws RequestInvalidTimestampException
     */
    public function validate(Request $request): void
    {
        $this->validateTimestamp($request);
        
        if ($this->disableSignatureValidation) {
            // WARNING: Signature validation disabled - use only in development/testing
            return;
        }
        
        try {
            $this->validateSignature($request);
        } catch (OutdatedCertExceptionException $e) {
            // load cert again and validate because temp file was outdated.
            $this->validateSignature($request);
        }
    }

    /**
     * Validate request timestamp. Request tolerance should be 150 seconds.
     * For more details @see https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/developing-an-alexa-skill-as-a-web-service#timestamp.
     *
     * @throws RequestInvalidTimestampException
     */
    private function validateTimestamp(Request $request): void
    {
        if (null === $request->request || !$request->request->validateTimestamp()) {
            return;
        }

        $differenceInSeconds = time() - $request->request->timestamp?->getTimestamp();

        if ($differenceInSeconds > $this->timestampTolerance) {
            throw new RequestInvalidTimestampException('Invalid timestamp.');
        }
    }

    /**
     * Validate request signature. The steps for signature validation are described at developer page.
     *
     * @see https://developer.amazon.com/public/solutions/alexa/alexa-skills-kit/docs/developing-an-alexa-skill-as-a-web-service#checking-the-signature-of-the-request
     *
     * @throws OutdatedCertExceptionException
     * @throws RequestInvalidSignatureException
     */
    private function validateSignature(Request $request): void
    {
        if (null === $request->request || !$request->request->validateSignature()) {
            return;
        }

        // validate cert url
        $this->certValidator->validateCertUrl($request);

        // fetch cert data using appropriate HTTP client
        $certData = $this->certValidator->fetchCertData($request, $this->createHttpClientCallback());

        // openssl cert validation
        $this->certValidator->verifyCert($request, $certData);

        // parse cert
        $certContent = $this->certValidator->parseCertData($certData);

        // validate cert
        $this->certValidator->validateCertContent($certContent, $request->signatureCertChainUrl);
    }

    /**
     * Create HTTP client callback that works with both PSR-18 and Guzzle clients
     */
    private function createHttpClientCallback(): callable
    {
        return function (string $url): array {
            if ($this->client instanceof Psr18ClientInterface) {
                // PSR-18 client
                $request = $this->createPsr18Request($url);
                $response = $this->client->sendRequest($request);
                
                return [
                    'status_code' => $response->getStatusCode(),
                    'body' => (string) $response->getBody(),
                ];
            } else {
                // Guzzle client
                $response = $this->client->request('GET', $url);
                
                return [
                    'status_code' => $response->getStatusCode(),
                    'body' => $response->getBody()->getContents(),
                ];
            }
        };
    }

    /**
     * Create PSR-7 request for PSR-18 clients
     */
    private function createPsr18Request(string $url): \Psr\Http\Message\RequestInterface
    {
        // Try to create PSR-7 request using available factories
        if (class_exists(\GuzzleHttp\Psr7\Request::class)) {
            return new \GuzzleHttp\Psr7\Request('GET', $url);
        }
        
        // Fallback - create simple request using PSR-17 factory if available
        if (class_exists(\Nyholm\Psr7\Factory\Psr17Factory::class)) {
            $factory = new \Nyholm\Psr7\Factory\Psr17Factory();
            return $factory->createRequest('GET', $url);
        }
        
        // If no PSR-7 implementation is available, throw an exception
        throw new \RuntimeException('No PSR-7 implementation found. Please install guzzlehttp/psr7 or nyholm/psr7.');
    }

    /**
     * Get certificate cache directory
     */
    public function getCertCacheDir(): string
    {
        return $this->certValidator->getCertCacheDir();
    }

    /**
     * Check if signature validation is disabled
     */
    public function isSignatureValidationDisabled(): bool
    {
        return $this->disableSignatureValidation;
    }

    /**
     * Get timestamp tolerance
     */
    public function getTimestampTolerance(): int
    {
        return $this->timestampTolerance;
    }

    /**
     * Get HTTP client
     */
    public function getClient()
    {
        return $this->client;
    }
}
