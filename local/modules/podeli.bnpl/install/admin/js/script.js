function showPodeliRefundForm(id) {
  new BX.CAdminDialog({
    content_url: "/bitrix/admin/podeli.bnpl_item.php?bxpublic=Y&lang=" +
      BX.message("LANGUAGE_ID") + "&id=" + id + "&sessid=" + BX.bitrix_sessid(),
    width: "920",
    height: "400",
    buttons: [
      new BX.CWindowButton({
        title: BX.message("PODELI.PAYMENT_JS_REFUND"),
        name: "refund",
        id: "refund_form",
        action: function() {
          BX("podeli_bnpl_refund_form").submit();
        },
      }),
      BX.CAdminDialog.btnCancel,
    ],
  }).Show();
}

function showPodeliCommitForm(id) {
  if (confirm(BX.message("PODELI.PAYMENT_JS_COMMIT_TITLE"))) {
    BX.ajax.post(
      "/bitrix/admin/podeli.bnpl_list.php",
      { "id": id, "action": "commit", "sessid": BX.message("bitrix_sessid") },
      function(data) {
        data = JSON.parse(data);
        if (data["result"]) {
          setTimeout(function() {
            window.location = window.location.href;
          }, 2000);
        } else {
          showErrors(data["errors"]);
        }
      },
    );
  }
}

function showPodeliCancelForm(id) {
  if (confirm(BX.message("PODELI.PAYMENT_JS_CANCEL_TITLE"))) {
    BX.ajax.post(
      "/bitrix/admin/podeli.bnpl_list.php",
      { "id": id, "action": "cancel", "sessid": BX.message("bitrix_sessid") },
      function(data) {
        data = JSON.parse(data);
        if (data["result"]) {
          setTimeout(function() {
            window.location = window.location.href;
          }, 2000);
        } else {
          showErrors(data["errors"]);
        }
      },
    );
  }
}

function showPodeliUpdateForm(id) {
  BX.ajax.post(
    "/bitrix/admin/podeli.bnpl_list.php",
    { "id": id, "action": "update", "sessid": BX.message("bitrix_sessid") },
    function(data) {
      data = JSON.parse(data);
      if (data["result"]) {
        setTimeout(function() {
          window.location = window.location.href;
        }, 2000);
      } else {
        showErrors(data["errors"]);
      }
    },
  );
}

function podeliBnplUpdateTotal() {
  let sum = 0;
  document.querySelectorAll(".podeli_bnpl_refund_position:checked").forEach(
    function(el) {
      sum += BX("podeli_bnpl_refund_quantity_" + el.value).value *
        BX("podeli_bnpl_refund_amount_" + el.value).value;
    },
  );
  BX("podeli_bnpl_total_refund_amount").innerHTML = sum.toFixed(2);
}

function showErrors(errors) {
  alert(errors.join("\n"));
}
