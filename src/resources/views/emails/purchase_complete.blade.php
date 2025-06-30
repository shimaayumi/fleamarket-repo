<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>取引完了通知</title>
</head>

<body>
    <p>{{ $sellerName }} 様</p>
    <br>
    <p>下記の商品の取引が完了しました。</p>
    <p>【商品情報】 </p>
    <p>取引ID: {{ $purchaseId }}</p>
    <p>商品名:{{ $itemName }}</p>
    <p>-------------------------------</p>
    <br>
    <p>coachtechフリマ</p>

</body>

</html>