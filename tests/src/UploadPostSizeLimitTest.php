<?php

namespace Test;

use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Exceptions\Halt;
use Slim\Psr7\Response;
use Tests\Abstract\TestCase;
use Tests\Traits\OpportunityBuilder;
use Tests\Traits\RequestFactory;
use Tests\Traits\UserDirector;

class UploadPostSizeLimitTest extends TestCase
{
    use OpportunityBuilder,
        RequestFactory,
        UserDirector;

    function testInformaExcessoDeTamanhoQuandoOPostFoiDescartado()
    {
        $limite = $this->app->getMaxUploadSize(false) * 1024;

        $resposta = $this->uploadSemArquivo($limite + 1);

        $this->assertEquals(400, $resposta['status'], 'Garantindo que o upload seja recusado');
        $this->assertEquals('O arquivo enviado é maior do que o permitido.', $resposta['data'], 'Garantindo que o motivo informado seja o tamanho do arquivo');
    }

    function testInformaAusenciaDeArquivoQuandoOPostCabeNoLimite()
    {
        $limite = $this->app->getMaxUploadSize(false) * 1024;

        $resposta = $this->uploadSemArquivo($limite - 1);

        $this->assertEquals(400, $resposta['status'], 'Garantindo que o upload seja recusado');
        $this->assertEquals('Nenhum arquivo enviado', $resposta['data'], 'Garantindo que o motivo informado seja a ausência de arquivo');
    }

    function testNaoTrataComoExcessoOPostQueBateExatamenteNoLimite()
    {
        $limite = $this->app->getMaxUploadSize(false) * 1024;

        $resposta = $this->uploadSemArquivo($limite);

        $this->assertEquals(400, $resposta['status'], 'Garantindo que o upload seja recusado');
        $this->assertEquals('Nenhum arquivo enviado', $resposta['data'], 'Garantindo que o tamanho igual ao limite não seja tratado como excesso');
    }

    /**
     * Simula o POST que o PHP descarta por exceder o post_max_size: $_FILES vazio e apenas o CONTENT_LENGTH denunciando o tamanho.
     * @return array status e data da resposta
     */
    private function uploadSemArquivo(int $content_length): array
    {
        $opportunity = $this->createOpportunity();
        $app = $this->app;

        $app->request = $this->requestFactory->mapasPOST('opportunity', 'upload', [$opportunity->id]);
        $app->response = new Response();

        $files = $_FILES;
        $length = $_SERVER['CONTENT_LENGTH'] ?? null;
        $_FILES = [];
        $_SERVER['CONTENT_LENGTH'] = $content_length;

        try {
            $controller = $app->controller('opportunity');
            $controller->setRequestData(['id' => $opportunity->id]);
            $controller->callAction('POST', 'upload', []);
            $this->fail('Garantindo que o upload sem arquivo encerre com Halt após responder em JSON');
        } catch (Halt) {
        } finally {
            $_FILES = $files;
            if ($length === null) {
                unset($_SERVER['CONTENT_LENGTH']);
            } else {
                $_SERVER['CONTENT_LENGTH'] = $length;
            }
        }

        $body = $app->response->getBody();
        $body->rewind();

        return [
            'status' => $app->response->getStatusCode(),
            'data' => json_decode($body->getContents(), true)['data'] ?? null,
        ];
    }

    private function createOpportunity(): Opportunity
    {
        $admin = $this->userDirector->createUser('admin');
        $this->login($admin);

        $this->opportunityBuilder
            ->reset(owner: $admin->profile, owner_entity: $admin->profile)
            ->fillRequiredProperties()
            ->save();

        return $this->opportunityBuilder->getInstance();
    }
}
