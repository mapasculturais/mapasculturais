<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\Entities\Registration $registration
 */

// Obter logo da plataforma
$logoUrl = '';
$logoImage = $app->config['logo.image'] ?? '';
if ($logoImage) {
    $logoPath = $app->view->resolveFilename('assets', $logoImage);
    if (file_exists($logoPath)) {
        $logoUrl = $logoPath;
    }
}

$siteName = $app->config['logo.title'] . ' ' . $app->config['logo.subtitle'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 2cm; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 11pt; 
            line-height: 1.5;
            color: #333;
        }
        .logo-container { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .logo-container img { 
            max-width: 250px; 
            max-height: 80px; 
        }
        .logo-container .site-name { 
            font-size: 24pt; 
            font-weight: bold; 
            color: #222; 
            margin: 10px 0;
        }
        .header { margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 18pt; color: #222; }
        .info-block { margin: 20px 0; }
        .info-block h2 { font-size: 14pt; color: #444; margin: 15px 0 10px; }
        .field { margin: 10px 0; padding: 10px; background: #f9f9f9; }
        .field-label { font-weight: bold; color: #555; display: block; margin-bottom: 5px; }
        .field-value { color: #333; }
        .attachment-icon { margin-right: 5px; }
        .attachment-link { text-decoration: underline; color: #0066cc; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #e9e9e9; font-weight: bold; }
    </style>
</head>
<body>

<?php $this->applyTemplateHook('registration-pdf', 'before'); ?>

<!-- Logo da plataforma -->
<?php $this->applyTemplateHook('registration-pdf-logo', 'before'); ?>
<div class="logo-container">
    <?php if ($logoUrl && file_exists($logoUrl)): ?>
        <?php
        $imageData = base64_encode(file_get_contents($logoUrl));
        $mimeType = mime_content_type($logoUrl);
        ?>
        <img src="data:<?= $mimeType ?>;base64,<?= $imageData ?>" alt="Logo">
    <?php else: ?>
        <div class="site-name"><?= htmlspecialchars($siteName) ?></div>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-logo', 'after'); ?>

<!-- Cabeçalho -->
<?php $this->applyTemplateHook('registration-pdf-header', 'before'); ?>
<div class="header">
    <h1>Ficha de Inscrição #<?= htmlspecialchars($registration->number) ?></h1>
    <p><strong>Oportunidade:</strong> <?= htmlspecialchars($registration->opportunity->name) ?></p>
    <p><strong>Responsável:</strong> <?= htmlspecialchars($registration->owner->name) ?></p>
    <?php if ($registration->sentTimestamp): ?>
        <p><strong>Data de envio:</strong> <?= $registration->sentTimestamp->format('d/m/Y H:i') ?></p>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-header', 'after'); ?>

<!-- Informações básicas -->
<?php $this->applyTemplateHook('registration-pdf-basic-info', 'before'); ?>
<div class="info-block">
    <h2>Informações Básicas</h2>
    
    <?php if ($registration->category): ?>
        <div class="field">
            <span class="field-label">Categoria:</span>
            <span class="field-value"><?= htmlspecialchars($registration->category) ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($registration->range): ?>
        <div class="field">
            <span class="field-label">Faixa/Linha:</span>
            <span class="field-value"><?= htmlspecialchars($registration->range) ?></span>
        </div>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-basic-info', 'after'); ?>

<!-- Campos personalizados -->
<?php 
$opportunity = $registration->opportunity;
$opportunity->registerRegistrationMetadata();
?>

<?php $this->applyTemplateHook('registration-pdf-fields', 'before'); ?>
<div class="info-block">
    <h2>Dados do Formulário</h2>
    
    <?php 
    // Combinar campos e arquivos na ordem correta
    $allFields = [];
    
    // Adicionar campos de formulário
    foreach ($opportunity->registrationFieldConfigurations as $field) {
        $allFields[] = [
            'type' => 'field',
            'config' => $field,
            'order' => $field->displayOrder ?? 999
        ];
    }
    
    // Adicionar campos de arquivo
    foreach ($opportunity->registrationFileConfigurations as $fileConfig) {
        $allFields[] = [
            'type' => 'file',
            'config' => $fileConfig,
            'order' => $fileConfig->displayOrder ?? 999
        ];
    }
    
    // Ordenar por displayOrder
    usort($allFields, function($a, $b) {
        return $a['order'] <=> $b['order'];
    });
    
    foreach ($allFields as $item):
        $config = $item['config'];
        
        if ($item['type'] === 'file'): 
            // Campos de arquivo - usa fileGroupName que retorna "rfc_{id}"
            $groupName = $config->fileGroupName;
            $file = $registration->files[$groupName] ?? null;
            if ($file): 
            ?>
                <div class="field">
                    <span class="field-label"><?= $config->required ? '* ' : '' ?><?= htmlspecialchars($config->title) ?>:</span>
                    <span class="field-value">
                        <span class="attachment-link"><span class="attachment-icon">▸</span><?= htmlspecialchars($file->description ?: $file->name) ?></span>
                    </span>
                </div>
            <?php endif; ?>
        <?php else:
            // Campos normais
            $fieldName = $config->fieldName;
            $value = $registration->$fieldName ?? null;
            
            if ($value !== null && $value !== ''): ?>
                <div class="field">
                    <span class="field-label"><?= $config->required ? '* ' : '' ?><?= htmlspecialchars($config->title) ?>:</span>
                    <span class="field-value">
                        <?php if (is_array($value)): ?>
                            <?= htmlspecialchars(implode(', ', $value)) ?>
                        <?php elseif (is_object($value)): ?>
                            <?= htmlspecialchars(json_encode($value, JSON_UNESCAPED_UNICODE)) ?>
                        <?php else: ?>
                            <?= nl2br(htmlspecialchars($value)) ?>
                        <?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php $this->applyTemplateHook('registration-pdf-fields', 'after'); ?>

<!-- Agentes relacionados -->
<?php if (count($registration->relatedAgents) > 0): ?>
    <?php $this->applyTemplateHook('registration-pdf-agents', 'before'); ?>
    <div class="info-block">
        <h2>Agentes Relacionados</h2>
        
        <?php foreach ($registration->relatedAgents as $group => $agents): ?>
            <?php foreach ($agents as $agent): ?>
                <div class="field">
                    <span class="field-label"><?= htmlspecialchars($group) ?>:</span>
                    <span class="field-value"><?= htmlspecialchars($agent->name) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </div>
    <?php $this->applyTemplateHook('registration-pdf-agents', 'after'); ?>
<?php endif; ?>

<?php $this->applyTemplateHook('registration-pdf', 'after'); ?>

</body>
</html>
