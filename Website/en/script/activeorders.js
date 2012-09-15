function OpenOrder(sRoot, nOrderID)
{
  document.location = sRoot + 'orderitems.php?id=' + nOrderID;
}

function NewOrder(sRoot, nCoopOrderID)
{
  document.location = sRoot + 'order.php?coid=' + nCoopOrderID;
}
