<?php

declare(strict_types=1);

require __DIR__ . '/payment_template.php';

renderPaymentPage(
    'Inscrição confirmada',
    'Pagamento confirmado com sucesso. Sua inscrição está concluída.',
    'success',
    'approved',
    6,
    'index.php'
);
