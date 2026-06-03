<?php

declare(strict_types=1);

require __DIR__ . '/payment_template.php';

renderPaymentPage(
    'Pagamento pendente',
    'Recebemos seu pedido de inscrição. O pagamento ainda está em processamento pelo Mercado Pago.',
    'pending',
    'pending'
);
