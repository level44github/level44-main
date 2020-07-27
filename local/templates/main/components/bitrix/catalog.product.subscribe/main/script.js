(function (window) {
  if (!!window.JCCatalogProductSubscribe) {
    return;
  }

  var subscribeButton = function (params) {
    subscribeButton.superclass.constructor.apply(this, arguments);
    this.nameNode = BX.create('span', {
      props: { id: this.id },
      style: typeof params.style === 'object' ? params.style : {},
      text: params.text,
    });
    this.buttonNode = BX.create('span', {
      attrs: { className: params.className },
      style: { marginBottom: '0', borderBottom: '0 none transparent' },
      children: [this.nameNode],
      events: this.contextEvents,
    });
    if (BX.browser.IsIE()) {
      this.buttonNode.setAttribute('hideFocus', 'hidefocus');
    }
  };
  BX.extend(subscribeButton, BX.PopupWindowButton);

  window.JCCatalogProductSubscribe = function (params) {
    this.buttonId = params.buttonId;
    this.buttonClass = params.buttonClass;
    this.jsObject = params.jsObject;
    this.ajaxUrl =
      '/bitrix/components/bitrix/catalog.product.subscribe/ajax.php';
    this.alreadySubscribed = params.alreadySubscribed;
    this.listIdAlreadySubscribed = params.listIdAlreadySubscribed;
    this.urlListSubscriptions = params.urlListSubscriptions;
    this.listOldItemId = {};
    this.landingId = params.landingId;

    this.elemButtonSubscribe = null;
    this.elemPopupWin = null;
    this.defaultButtonClass = 'bx-catalog-subscribe-button';

    this._elemButtonSubscribeClickHandler = BX.delegate(this.subscribe, this);
    this._elemHiddenClickHandler = BX.delegate(this.checkSubscribe, this);

    BX.ready(BX.delegate(this.init, this));
  };

  window.JCCatalogProductSubscribe.prototype.init = function () {
    if (!!this.buttonId) {
      this.elemButtonSubscribe = BX(this.buttonId);
      this.elemHiddenSubscribe = BX(this.buttonId + '_hidden');
    }

    if (!!this.elemButtonSubscribe) {
      BX.bind(
        this.elemButtonSubscribe,
        'click',
        this._elemButtonSubscribeClickHandler,
      );
    }

    if (!!this.elemHiddenSubscribe) {
      BX.bind(this.elemHiddenSubscribe, 'click', this._elemHiddenClickHandler);
    }

    this.setButton(this.alreadySubscribed);
    this.setIdAlreadySubscribed(this.listIdAlreadySubscribed);
  };

  window.JCCatalogProductSubscribe.prototype.checkSubscribe = function () {
    if (!this.elemHiddenSubscribe || !this.elemButtonSubscribe) return;

    if (
      this.listOldItemId.hasOwnProperty(this.elemButtonSubscribe.dataset.item)
    ) {
      this.setButton(true);
    } else {
      BX.ajax({
        method: 'POST',
        dataType: 'json',
        url: this.ajaxUrl,
        data: {
          sessid: BX.bitrix_sessid(),
          checkSubscribe: 'Y',
          itemId: this.elemButtonSubscribe.dataset.item,
        },
        onsuccess: BX.delegate(function (result) {
          if (result.subscribe) {
            this.setButton(true);
            this.listOldItemId[this.elemButtonSubscribe.dataset.item] = true;
          } else {
            this.setButton(false);
          }
        }, this),
      });
    }
  };

  window.JCCatalogProductSubscribe.prototype.subscribe = function () {
    this.elemButtonSubscribe = BX.proxy_context;
    if (!this.elemButtonSubscribe) return false;

    BX.ajax({
      method: 'POST',
      dataType: 'json',
      url: this.ajaxUrl,
      data: {
        sessid: BX.bitrix_sessid(),
        subscribe: 'Y',
        itemId: this.elemButtonSubscribe.dataset.item,
        siteId: BX.message('SITE_ID'),
        landingId: this.landingId,
      },
      onsuccess: BX.delegate(function (result) {
        if (result.success) {
          this.createSuccessPopup(result);
          this.setButton(true);
        } else if (result.contactFormSubmit) {
          this.showFormModal(result);
        } else if (result.error) {
          if (result.hasOwnProperty('setButton')) {
            this.setButton(true);
          }
          this.showWindowWithAnswer({
            status: 'error',
            message: result.message,
          });
        }
      }, this),
    });
  };

  window.JCCatalogProductSubscribe.prototype.validateContactField = function (
    contactTypeData,
  ) {
    var inputFields = BX.findChildren(
      BX('bx-catalog-subscribe-form-div'),
      { tag: 'input', attribute: { id: 'subscribe-email' } },
      true,
    );
    if (!inputFields.length || typeof contactTypeData !== 'object') {
      BX('bx-catalog-subscribe-form-notify').style.color = 'red';
      BX('bx-catalog-subscribe-form-notify').innerHTML = BX.message(
        'CPST_SUBSCRIBE_VALIDATE_UNKNOW_ERROR',
      );
      return false;
    }

    var contactTypeId,
      contactValue,
      useContact,
      errors = [],
      useContactErrors = [];
    for (var k = 0; k < inputFields.length; k++) {
      contactTypeId = inputFields[k].getAttribute('data-id');
      contactValue = inputFields[k].value;
      useContact = BX('bx-contact-use-' + contactTypeId);
      if (useContact && useContact.value == 'N') {
        useContactErrors.push(true);
        continue;
      }
      if (!contactValue.length) {
        errors.push(
          BX.message('CPST_SUBSCRIBE_VALIDATE_ERROR_EMPTY_FIELD').replace(
            '#FIELD#',
            contactTypeData[contactTypeId].contactLable,
          ),
        );
      }
    }

    if (inputFields.length == useContactErrors.length) {
      BX('bx-catalog-subscribe-form-notify').style.color = 'red';
      BX('bx-catalog-subscribe-form-notify').innerHTML = BX.message(
        'CPST_SUBSCRIBE_VALIDATE_ERROR',
      );
      return false;
    }

    if (errors.length) {
      BX('bx-catalog-subscribe-form-notify').style.color = 'red';
      for (var i = 0; i < errors.length; i++) {
        BX('bx-catalog-subscribe-form-notify').innerHTML = errors[i];
      }
      return false;
    }

    return true;
  };

  window.JCCatalogProductSubscribe.prototype.reloadCaptcha = function () {
    BX.ajax.get(this.ajaxUrl + '?reloadCaptcha=Y', '', function (captchaCode) {
      BX('captcha_sid').value = captchaCode;
      BX('captcha_img').src =
        '/bitrix/tools/captcha.php?captcha_sid=' + captchaCode + '';
    });
  };

  window.JCCatalogProductSubscribe.prototype.createContentForPopup = function (
    responseData,
  ) {
    if (!responseData.hasOwnProperty('contactTypeData')) {
      return null;
    }

    var contactTypeData = responseData.contactTypeData,
      contactCount = Object.keys(contactTypeData).length,
      styleInputForm = '',
      manyContact = 'N',
      content = document.createDocumentFragment();

    content.appendChild(
      BX.create('div', {
        props: {
          id: 'bx-catalog-subscribe-form-div',
          className: 'form-group',
        },
        children: [
          BX.create('p', {
            props: {
              id: 'bx-catalog-subscribe-form-notify',
            },
            text: '',
          }),
          BX.create('label', {
            props: {
              className: 'sr-only',
            },
            attrs: {
              for: 'subscribe-email',
            },
            text: 'E-mail',
          }),
          BX.create('input', {
            props: {
              id: 'subscribe-email',
              className: 'form-control',
              type: 'text',
              name: 'contact[1][user]',
              placeholder: 'E-mail',
            },
            attrs: { 'data-id': 1 },
          }),
          BX.create('p', {
            props: {
              id: 'bx-catalog-subscribe-form-notify',
            },
            text: '',
          }),
          BX.create('label', {
            props: {
              className: 'sr-only',
            },
            attrs: {
              for: 'subscribe-tel',
            },
            text: 'Телефон',
          }),
          BX.create('input', {
            props: {
              id: 'subscribe-tel',
              className: 'form-control',
              type: 'text',
              name: 'contact[1][user]',
              placeholder: 'Телефон',
            },
            attrs: { 'data-id': 1 },
          }),
        ],
      }),
    );

    var form = BX.create('form', {
      props: {
        id: 'bx-catalog-subscribe-form',
      },
      children: [
        BX.create('input', {
          props: {
            type: 'hidden',
            name: 'manyContact',
            value: manyContact,
          },
        }),
        BX.create('input', {
          props: {
            type: 'hidden',
            name: 'sessid',
            value: BX.bitrix_sessid(),
          },
        }),
        BX.create('input', {
          props: {
            type: 'hidden',
            name: 'itemId',
            value: this.elemButtonSubscribe.dataset.item,
          },
        }),
        BX.create('input', {
          props: {
            type: 'hidden',
            name: 'landingId',
            value: this.landingId,
          },
        }),
        BX.create('input', {
          props: {
            type: 'hidden',
            name: 'siteId',
            value: BX.message('SITE_ID'),
          },
        }),
        BX.create('input', {
          props: {
            type: 'hidden',
            name: 'contactFormSubmit',
            value: 'Y',
          },
        }),
      ],
    });

    form.appendChild(content);
    form.appendChild(
      BX.create('button', {
        props: {
          type: 'submit',
          className: 'js-subscribe-button btn btn-dark btn-block',
        },
        text: BX.message('CPST_SUBSCRIBE_BUTTON_NAME'),
        events: {
          click: BX.delegate(function (e) {
            e.preventDefault();
            if (!this.validateContactField(responseData.contactTypeData)) {
              return false;
            }

            BX.ajax.submitAjax(form, {
              method: 'POST',
              url: this.ajaxUrl,
              processData: true,
              onsuccess: BX.delegate(function (resultForm) {
                resultForm = BX.parseJSON(resultForm, {});
                if (resultForm.success) {
                  this.createSuccessPopup(resultForm);
                  this.setButton(true);
                  this.listOldItemId[
                    this.elemButtonSubscribe.dataset.item
                  ] = true;
                } else if (resultForm.error) {
                  if (resultForm.hasOwnProperty('setButton')) {
                    this.listOldItemId[
                      this.elemButtonSubscribe.dataset.item
                    ] = true;
                    this.setButton(true);
                  }
                  var errorMessage = resultForm.message;
                  if (resultForm.hasOwnProperty('typeName')) {
                    errorMessage = resultForm.message.replace(
                      'USER_CONTACT',
                      resultForm.typeName,
                    );
                  }
                  BX('bx-catalog-subscribe-form-notify').style.color = 'red';
                  BX(
                    'bx-catalog-subscribe-form-notify',
                  ).innerHTML = errorMessage;
                }
              }, this),
            });
          }, this),
        },
      }),
    );

    return form;
  };

  window.JCCatalogProductSubscribe.prototype.selectContactType = function (
    contactTypeId,
    event,
  ) {
    var contactInput = BX(
        'bx-catalog-subscribe-form-container-' + contactTypeId,
      ),
      visibility = '',
      checkboxInput = BX('bx-contact-checkbox-' + contactTypeId);
    if (!contactInput) {
      return false;
    }

    if (checkboxInput != event.target) {
      if (checkboxInput.checked) {
        checkboxInput.checked = false;
      } else {
        checkboxInput.checked = true;
      }
    }

    if (contactInput.currentStyle) {
      visibility = contactInput.currentStyle.display;
    } else if (window.getComputedStyle) {
      var computedStyle = window.getComputedStyle(contactInput, null);
      visibility = computedStyle.getPropertyValue('display');
    }

    if (visibility === 'none') {
      BX('bx-contact-use-' + contactTypeId).value = 'Y';
      BX.style(contactInput, 'display', '');
    } else {
      BX('bx-contact-use-' + contactTypeId).value = 'N';
      BX.style(contactInput, 'display', 'none');
    }
  };

  window.JCCatalogProductSubscribe.prototype.createSuccessPopup = function (
    result,
  ) {
    this.showSuccessModal(result.message);
  };

  window.JCCatalogProductSubscribe.prototype.showSuccessModal = function (
    successText,
  ) {
    var $subscribeSucModal = $('.js-subscribe-modal .js-subscribe-suc');
    $('.js-subscribe-modal .modal-dialog').hide();
    $subscribeSucModal.find('.js-text').text(successText);
    $subscribeSucModal.show();
    if (!$('.js-subscribe-modal').hasClass('show')) {
      $('.modal-toggle').click();
    }
  };

  window.JCCatalogProductSubscribe.prototype.showFormModal = function (result) {
    var form = this.createContentForPopup(result);
    if (!form) {
      return false;
    }
    var subscribeFormBody = document.querySelector('.js-subscribe-form-body');
    if (!subscribeFormBody) {
      return false;
    }

    $(subscribeFormBody).find('form').remove();
    subscribeFormBody.appendChild(form);

    $('.js-subscribe-modal .modal-dialog').hide();
    $('.js-subscribe-modal .js-subscribe-form').show();

    if (!$('.js-subscribe-modal').hasClass('show')) {
      $('.modal-toggle').click();
    }
  };

  window.JCCatalogProductSubscribe.prototype.closeModal = function () {
    if ($('.js-subscribe-modal').hasClass('show')) {
      $('.modal-toggle').click();
    }
  };

  window.JCCatalogProductSubscribe.prototype.initPopupWindow = function () {
    this.elemPopupWin = BX.PopupWindowManager.create(
      'CatalogSubscribe_' + this.buttonId,
      null,
      {
        autoHide: false,
        offsetLeft: 0,
        offsetTop: 0,
        overlay: true,
        closeByEsc: true,
        titleBar: true,
        closeIcon: true,
        contentColor: 'white',
      },
    );
  };

  window.JCCatalogProductSubscribe.prototype.setButton = function (
    statusSubscription,
  ) {
    this.alreadySubscribed = Boolean(statusSubscription);
    if (this.alreadySubscribed) {
      this.elemButtonSubscribe.className =
        this.buttonClass + ' ' + this.defaultButtonClass + ' disabled';
      this.elemButtonSubscribe.innerHTML =
        '<span>' + BX.message('CPST_TITLE_ALREADY_SUBSCRIBED') + '</span>';
      BX.unbind(
        this.elemButtonSubscribe,
        'click',
        this._elemButtonSubscribeClickHandler,
      );
    } else {
      this.elemButtonSubscribe.className =
        this.buttonClass + ' ' + this.defaultButtonClass;
      this.elemButtonSubscribe.innerHTML =
        '<symbol width="14" height="16" viewBox="0 0 14 16" xmlns="http://www.w3.org/2000/svg"><path d="M1.5791 12.624H8.46289C8.4082 13.5469 7.82031 14.1348 6.99316 14.1348C6.17285 14.1348 5.57812 13.5469 5.53027 12.624H4.46387C4.51855 13.9365 5.55078 15.0918 6.99316 15.0918C8.44238 15.0918 9.47461 13.9434 9.5293 12.624H12.4141C13.0566 12.624 13.4463 12.2891 13.4463 11.7969C13.4463 11.1133 12.749 10.498 12.1611 9.88965C11.71 9.41797 11.5869 8.44727 11.5322 7.66113C11.4844 4.96777 10.7871 3.22461 8.96875 2.56836C8.73633 1.67969 8.00488 0.96875 6.99316 0.96875C5.98828 0.96875 5.25 1.67969 5.02441 2.56836C3.20605 3.22461 2.50879 4.96777 2.46094 7.66113C2.40625 8.44727 2.2832 9.41797 1.83203 9.88965C1.2373 10.498 0.546875 11.1133 0.546875 11.7969C0.546875 12.2891 0.929688 12.624 1.5791 12.624ZM1.87305 11.5918V11.5098C1.99609 11.3047 2.40625 10.9082 2.76172 10.5049C3.25391 9.95801 3.48633 9.08301 3.54785 7.74316C3.60254 4.7627 4.49121 3.80566 5.66016 3.49121C5.83105 3.4502 5.92676 3.36133 5.93359 3.19043C5.9541 2.47266 6.36426 1.97363 6.99316 1.97363C7.62891 1.97363 8.03223 2.47266 8.05957 3.19043C8.06641 3.36133 8.15527 3.4502 8.32617 3.49121C9.50195 3.80566 10.3906 4.7627 10.4453 7.74316C10.5068 9.08301 10.7393 9.95801 11.2246 10.5049C11.5869 10.9082 11.9902 11.3047 12.1133 11.5098V11.5918H1.87305Z" fill="#212121"/></symbol>';
      '<span>' + BX.message('CPST_SUBSCRIBE_BUTTON_NAME') + '</span>';
      BX.bind(
        this.elemButtonSubscribe,
        'click',
        this._elemButtonSubscribeClickHandler,
      );
    }
  };

  window.JCCatalogProductSubscribe.prototype.setIdAlreadySubscribed = function (
    listIdAlreadySubscribed,
  ) {
    if (BX.type.isPlainObject(listIdAlreadySubscribed)) {
      this.listOldItemId = listIdAlreadySubscribed;
    }
  };

  window.JCCatalogProductSubscribe.prototype.showWindowWithAnswer = function (
    answer,
  ) {
    answer = answer || {};
    if (!answer.message) {
      if (answer.status == 'success') {
        answer.message = BX.message('CPST_STATUS_SUCCESS');
      } else {
        answer.message = BX.message('CPST_STATUS_ERROR');
      }
    }
    var messageBox = BX.create('div', {
      props: {
        className: 'bx-catalog-subscribe-alert',
      },
      children: [
        BX.create('span', {
          props: {
            className: 'bx-catalog-subscribe-aligner',
          },
        }),
        BX.create('span', {
          props: {
            className: 'bx-catalog-subscribe-alert-text',
          },
          text: answer.message,
        }),
        BX.create('div', {
          props: {
            className: 'bx-catalog-subscribe-alert-footer',
          },
        }),
      ],
    });
    var currentPopup = BX.PopupWindowManager.getCurrentPopup();
    if (currentPopup) {
      currentPopup.destroy();
    }
    var idTimeout = setTimeout(function () {
      var w = BX.PopupWindowManager.getCurrentPopup();
      if (!w || w.uniquePopupId != 'bx-catalog-subscribe-status-action') {
        return;
      }
      w.close();
      w.destroy();
    }, 3500);
    var popupConfirm = BX.PopupWindowManager.create(
      'bx-catalog-subscribe-status-action',
      null,
      {
        content: messageBox,
        onPopupClose: function () {
          this.destroy();
          clearTimeout(idTimeout);
        },
        autoHide: true,
        zIndex: 2000,
        className: 'bx-catalog-subscribe-alert-popup',
      },
    );
    popupConfirm.show();
    BX('bx-catalog-subscribe-status-action').onmouseover = function (e) {
      clearTimeout(idTimeout);
    };
    BX('bx-catalog-subscribe-status-action').onmouseout = function (e) {
      idTimeout = setTimeout(function () {
        var w = BX.PopupWindowManager.getCurrentPopup();
        if (!w || w.uniquePopupId != 'bx-catalog-subscribe-status-action') {
          return;
        }
        w.close();
        w.destroy();
      }, 3500);
    };
  };
})(window);
