<?php

declare(strict_types=1);

namespace Rboschin\AmazonAlexa\Validation;

use Rboschin\AmazonAlexa\Exception\OutdatedCertExceptionException;
use Rboschin\AmazonAlexa\Exception\RequestInvalidSignatureException;
use Rboschin\AmazonAlexa\Request\Request;

/**
 * CertValidator handles certificate validation logic
 * 
 * This class is responsible for:
 * - Fetching and caching certificates
 * - Validating certificate content and timestamps
 * - Parsing certificate data
 */
class CertValidator
{
    private string $certCacheDir;

    public function __construct(?string $certCacheDir = null)
    {
        $this->certCacheDir = $certCacheDir ?? sys_get_temp_dir();
    }

    /**
     * Validate certificate URL format
     */
    public function validateCertUrl(Request $request): void
    {
        if (false === (bool) preg_match("/https:\/\/s3.amazonaws.com(\:443)?\/echo.api\/*/i", $request->signatureCertChainUrl)) {
            throw new RequestInvalidSignatureException('Invalid cert url.');
        }
    }

    /**
     * Fetch certificate data from URL or cache
     */
    public function fetchCertData(Request $request, callable $httpClient): string
    {
        $localCertPath = $this->getLocalCertPath($request->signatureCertChainUrl);

        if (!file_exists($localCertPath)) {
            $response = $httpClient($request->signatureCertChainUrl);

            if ($response['status_code'] !== 200) {
                throw new RequestInvalidSignatureException('Can\'t fetch cert from URL.');
            }

            $certData = $response['body'];
            @file_put_contents($localCertPath, $certData);
        } else {
            $certData = @file_get_contents($localCertPath);
        }

        if ($certData === false) {
            throw new RequestInvalidSignatureException('Failed to read certificate data.');
        }

        return $certData;
    }

    /**
     * Verify certificate signature
     */
    public function verifyCert(Request $request, string $certData): void
    {
        if (1 !== @openssl_verify($request->amazonRequestBody, base64_decode($request->signature, true), $certData, 'sha1')) {
            throw new RequestInvalidSignatureException('Cert ssl verification failed.');
        }
    }

    /**
     * Parse certificate data
     */
    public function parseCertData(string $certData): array
    {
        $certContent = @openssl_x509_parse($certData);
        if (empty($certContent)) {
            throw new RequestInvalidSignatureException('Parse cert failed.');
        }

        return $certContent;
    }

    /**
     * Validate certificate content
     */
    public function validateCertContent(array $cert, string $certUrl): void
    {
        $this->validateCertSubject($cert);
        $this->validateCertValidTime($cert, $certUrl);
    }

    /**
     * Validate certificate subject
     */
    private function validateCertSubject(array $cert): void
    {
        if (false === isset($cert['extensions']['subjectAltName']) ||
            false === stristr($cert['extensions']['subjectAltName'], 'echo-api.amazon.com')
        ) {
            throw new RequestInvalidSignatureException('Cert subject error.');
        }
    }

    /**
     * Validate certificate validity time
     */
    private function validateCertValidTime(array $cert, string $certUrl): void
    {
        if (false === isset($cert['validTo_time_t']) || time() > $cert['validTo_time_t'] || false === isset($cert['validFrom_time_t']) || time() < $cert['validFrom_time_t']) {
            $localCertPath = $this->getLocalCertPath($certUrl);
            if (file_exists($localCertPath)) {
                /* @scrutinizer ignore-unhandled */ @unlink($localCertPath);
            }
            throw new OutdatedCertExceptionException('Cert is outdated.');
        }
    }

    /**
     * Get local certificate file path
     */
    private function getLocalCertPath(string $certUrl): string
    {
        return $this->certCacheDir . DIRECTORY_SEPARATOR . md5($certUrl) . '.pem';
    }

    /**
     * Get certificate cache directory
     */
    public function getCertCacheDir(): string
    {
        return $this->certCacheDir;
    }
}
