<?php

namespace Seals\Jobs;

use DateTimeImmutable;
use DateTimeZone;
use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\SealRelation;
use MapasCulturais\Entities\SealRelationField;
use MapasCulturais\Entities\User;
use MapasCulturais\i;

/**
 * Job diário que identifica campos de selos expirados ou prestes a expirar
 * e envia notificações idempotentes apenas para o dono da entidade.
 *
 * Para selos sensíveis/ocultos, o conteúdo da notificação é genérico,
 * sem expor o nome do selo ou do campo.
 */
class NotifySealExpirations extends JobType
{
    const SLUG = 'NotifySealExpirations';

    /** @var int Número de registros processados por batch. */
    const BATCH_SIZE = 100;

    /** @var int Iterações para execução diária por 100 anos. */
    const DAILY_ITERATIONS = 36500;

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return self::SLUG;
    }

    protected function _execute(\MapasCulturais\Entities\Job $job)
    {
        $app = App::i();

        $app->disableAccessControl();
        try {
            $this->sendExpiredNotifications();
            $this->sendAboutToExpireNotifications();
        } catch (\Exception $e) {
            $app->log->error("NotifySealExpirations error: " . $e->getMessage());
            $app->log->error($e->getTraceAsString());
        } finally {
            $app->enableAccessControl();
        }

        return true;
    }

    /**
     * Envia notificações para campos já expirados.
     *
     * Condição: expiry_date < CURRENT_DATE (UTC) E notified_expire = FALSE.
     *
     * @return void
     */
    protected function sendExpiredNotifications(): void
    {
        $app = App::i();
        $today = $this->getTodayUtc();

        do {
            $qb = $app->em->createQueryBuilder();
            $qb->select('srf')
                ->from(SealRelationField::class, 'srf')
                ->where('srf.expiryDate < :today')
                ->andWhere('srf.notifiedExpire = false')
                ->andWhere('srf.expiryDate IS NOT NULL')
                ->setParameter('today', $today, 'date')
                ->setMaxResults(self::BATCH_SIZE);

            /** @var SealRelationField[] $fields */
            $fields = $qb->getQuery()->getResult();

            foreach ($fields as $field) {
                $this->sendNotification($field, 'expired');
                $field->setNotifiedExpire(true);

                $sealRelation = $field->sealRelation;
                if ($sealRelation) {
                    $sealRelation->updateComputedStatus();
                    $app->em->persist($sealRelation);
                }

                $app->em->persist($field);
            }

            if (!empty($fields)) {
                $app->em->flush();
                $app->em->clear();
            }
        } while (count($fields) === self::BATCH_SIZE);
    }

    /**
     * Envia notificações para campos prestes a expirar (janela de 7 dias).
     *
     * Condição: expiry_date entre CURRENT_DATE (UTC) e CURRENT_DATE + 7 dias (UTC)
     *           E notified_to_expire = FALSE.
     *
     * @return void
     */
    protected function sendAboutToExpireNotifications(): void
    {
        $app = App::i();
        $today = $this->getTodayUtc();
        $warningDate = $today->modify('+7 days');

        do {
            $qb = $app->em->createQueryBuilder();
            $qb->select('srf')
                ->from(SealRelationField::class, 'srf')
                ->where('srf.expiryDate >= :today')
                ->andWhere('srf.expiryDate <= :warningDate')
                ->andWhere('srf.notifiedToExpire = false')
                ->andWhere('srf.expiryDate IS NOT NULL')
                ->setParameter('today', $today, 'date')
                ->setParameter('warningDate', $warningDate, 'date')
                ->setMaxResults(self::BATCH_SIZE);

            /** @var SealRelationField[] $fields */
            $fields = $qb->getQuery()->getResult();

            foreach ($fields as $field) {
                $this->sendNotification($field, 'about_to_expire');
                $field->setNotifiedToExpire(true);
                $app->em->persist($field);
            }

            if (!empty($fields)) {
                $app->em->flush();
                $app->em->clear();
            }
        } while (count($fields) === self::BATCH_SIZE);
    }

    /**
     * Cria e persiste a notificação para o dono da entidade.
     *
     * @param SealRelationField $field
     * @param string $type 'expired' | 'about_to_expire'
     * @return void
     */
    protected function sendNotification(SealRelationField $field, string $type): void
    {
        $sealRelation = $field->sealRelation;
        if (!$sealRelation instanceof SealRelation) {
            return;
        }

        $seal = $sealRelation->seal;
        if (!$seal) {
            return;
        }

        $entity = $sealRelation->owner;
        if (!$entity) {
            return;
        }

        $user = $entity->getOwnerUser();
        if (!$user instanceof User || $user->is('guest')) {
            return;
        }

        $isSensitive = (bool) $seal->sensitive;

        if ($isSensitive) {
            $message = i::__('Um selo sensível aplicado à sua entidade teve uma alteração de status. Acesse sua página para mais detalhes.');
        } else {
            $fieldName = $field->fieldName;
            $sealName = $seal->name;
            $date = $field->expiryDate->format(i::__('d/m/Y'));

            if ($type === 'expired') {
                $message = sprintf(i::__('O campo %s do selo %s expirou em %s.'), $fieldName, $sealName, $date);
            } else {
                $message = sprintf(i::__('O campo %s do selo %s expira em %s.'), $fieldName, $sealName, $date);
            }
        }

        $notification = new Notification();
        $notification->user = $user;
        $notification->message = $message;
        $notification->save();
    }

    /**
     * Retorna a data atual em UTC com hora zerada.
     *
     * @return DateTimeImmutable
     */
    protected function getTodayUtc(): DateTimeImmutable
    {
        return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->setTime(0, 0, 0);
    }
}
