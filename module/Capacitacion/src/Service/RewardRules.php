<?php
declare(strict_types=1);

namespace Capacitacion\Service;

final class RewardRules
{
    public function normalizeAndValidate(array $quizData): array
    {
        $mode = $quizData['reward_mode'] ?? 'none';
        if (!in_array($mode, ['none','points','product'], true)) {
            throw new \DomainException('reward_mode inválido');
        }

        $quizData['reward_product_id'] = ($quizData['reward_product_id'] ?? '') !== '' ? (int)$quizData['reward_product_id'] : null;

        // inputs text => sanitize digits
        $points = preg_replace('/\D+/', '', (string)($quizData['reward_points'] ?? ''));
        $limit  = preg_replace('/\D+/', '', (string)($quizData['reward_limit'] ?? '0'));

        $quizData['reward_points'] = $points !== '' ? (int)$points : null;
        $quizData['reward_limit']  = $limit !== '' ? (int)$limit : 0;

        if ($mode === 'none') {
            $quizData['reward_product_id'] = null;
            $quizData['reward_points'] = null;
        }

        if ($mode === 'points') {
            if (!$quizData['reward_points'] || $quizData['reward_points'] <= 0) {
                throw new \DomainException('Si el premio es puntos, reward_points debe ser > 0');
            }
            $quizData['reward_product_id'] = null;
        }

        if ($mode === 'product') {
            if (!$quizData['reward_product_id'] || $quizData['reward_product_id'] <= 0) {
                throw new \DomainException('Si el premio es e-rewards, selecciona un producto');
            }
            $quizData['reward_points'] = null;
        }

        unset($quizData['reward_awarded_count']);
        return $quizData;
    }
}
