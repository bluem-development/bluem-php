<?php

namespace Bluem\BluemPHP\Tests\Unit;

use Bluem\BluemPHP\Validators\WebhookSignatureValidation;
use Bluem\BluemPHP\Validators\WebhookValidator;
use DOMDocument;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;

class WebhookSignatureValidationTest extends TestCase
{
    private string $privateKeyFilePath;
    private string $certificateFilePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetWebhookValidatorState();
        $this->createTemporaryCertificate();
    }

    protected function tearDown(): void
    {
        @unlink($this->privateKeyFilePath);
        @unlink($this->certificateFilePath);

        parent::tearDown();
    }

    public function testValidRsaSha256SignaturePasses(): void
    {
        $validator = $this->createValidator();

        $validator->validate($this->createSignedXml(XMLSecurityKey::RSA_SHA256, XMLSecurityDSig::SHA256));

        $this->assertTrue($validator::$isValid, $validator->errorMessage());
    }

    public function testValidRsaSha512SignaturePasses(): void
    {
        $validator = $this->createValidator();

        $validator->validate($this->createSignedXml(XMLSecurityKey::RSA_SHA512, XMLSecurityDSig::SHA512));

        $this->assertTrue($validator::$isValid, $validator->errorMessage());
    }

    public function testTamperedSignedContentFailsReferenceValidation(): void
    {
        $validator = $this->createValidator();
        $signedXml = $this->createSignedXml(XMLSecurityKey::RSA_SHA256, XMLSecurityDSig::SHA256);

        $validator->validate(str_replace('<Status>valid</Status>', '<Status>tampered</Status>', $signedXml));

        $this->assertFalse($validator::$isValid);
        $this->assertStringContainsString('Reference Validation Failed', $validator->errorMessage());
    }

    public function testTamperedSignatureValueFailsSignatureValidation(): void
    {
        $validator = $this->createValidator();

        $validator->validate($this->tamperSignatureValue(
            $this->createSignedXml(XMLSecurityKey::RSA_SHA256, XMLSecurityDSig::SHA256)
        ));

        $this->assertFalse($validator::$isValid);
        $this->assertStringContainsString('Invalid signature', $validator->errorMessage());
    }

    public function testMissingSignatureAlgorithmFailsBeforeVerification(): void
    {
        $validator = $this->createValidator();

        $validator->validate($this->removeSignatureMethodAlgorithm(
            $this->createSignedXml(XMLSecurityKey::RSA_SHA256, XMLSecurityDSig::SHA256)
        ));

        $this->assertFalse($validator::$isValid);
        $this->assertStringContainsString('Unable to determine signature key algorithm', $validator->errorMessage());
    }

    private function createTemporaryCertificate(): void
    {
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        $this->assertNotFalse($privateKey);
        $this->assertTrue(openssl_pkey_export($privateKey, $privateKeyPem));

        $csr = openssl_csr_new([
            'commonName' => 'webhook-signature-validation.test',
        ], $privateKey);
        $this->assertNotFalse($csr);

        $certificate = openssl_csr_sign($csr, null, $privateKey, 1);
        $this->assertNotFalse($certificate);
        $this->assertTrue(openssl_x509_export($certificate, $certificatePem));

        $this->privateKeyFilePath = tempnam(sys_get_temp_dir(), 'bluem-private-key-');
        $this->certificateFilePath = tempnam(sys_get_temp_dir(), 'bluem-certificate-');

        $this->assertIsString($this->privateKeyFilePath);
        $this->assertIsString($this->certificateFilePath);
        $this->assertNotFalse(file_put_contents($this->privateKeyFilePath, $privateKeyPem));
        $this->assertNotFalse(file_put_contents($this->certificateFilePath, $certificatePem));
    }

    private function createSignedXml(string $signatureAlgorithm, string $digestAlgorithm): string
    {
        $doc = new DOMDocument('1.0', 'UTF-8');
        $doc->loadXML('<Envelope><Payload><Status>valid</Status></Payload></Envelope>');

        $signature = new XMLSecurityDSig();
        $signature->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $signature->addReference($doc, $digestAlgorithm, [XMLSecurityDSig::EXC_C14N], ['force_uri' => true]);

        $privateKey = new XMLSecurityKey($signatureAlgorithm, ['type' => 'private']);
        $privateKey->loadKey($this->privateKeyFilePath, true);

        $signature->sign($privateKey);
        $signature->appendSignature($doc->documentElement);

        return $doc->saveXML();
    }

    private function tamperSignatureValue(string $signedXml): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($signedXml);

        $signatureValue = $doc->getElementsByTagName('SignatureValue')->item(0);
        $signatureValue->nodeValue = 'A' . substr($signatureValue->nodeValue, 1);

        return $doc->saveXML();
    }

    private function removeSignatureMethodAlgorithm(string $signedXml): string
    {
        $doc = new DOMDocument();
        $doc->loadXML($signedXml);

        $signatureMethod = $doc->getElementsByTagName('SignatureMethod')->item(0);
        $signatureMethod->removeAttribute('Algorithm');

        return $doc->saveXML();
    }

    private function createValidator(): WebhookSignatureValidation
    {
        return new class('test', $this->certificateFilePath) extends WebhookSignatureValidation {
            public function __construct(string $env, private string $certificateFilePath)
            {
                parent::__construct($env);
            }

            protected function getPublicKeyFilePath(): string
            {
                return $this->certificateFilePath;
            }
        };
    }

    private function resetWebhookValidatorState(): void
    {
        $isValid = new ReflectionProperty(WebhookValidator::class, 'isValid');
        $isValid->setAccessible(true);
        $isValid->setValue(null, true);

        $errors = new ReflectionProperty(WebhookValidator::class, 'errors');
        $errors->setAccessible(true);
        $errors->setValue(null, []);
    }
}
