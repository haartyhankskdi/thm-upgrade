const BrippoAdmin = {
    wasInitialized: false,
    config: null,
    formKey: null,
    recoverOrder: {
        modalTitle: 'Recover order with Brippo',
        modal: null,
        modalForm: {
            inputOrderIncrementId: null,
            inputCustomerEmail: null,
            inputMessage: null,
            recoverLink: null,
            buttonSubmit: null,
            feedbackSuccess: null,
            feedbackError: null,
        },
        orderId: null,
        initModal() {
            if (this.modal) {
                return;
            }

            this.modal = document.createElement('div');
            this.modal.className = 'brippoAdminModal';
            this.modal.id = 'brippoRecoverOrderModal';

            const box = document.createElement('div');
            box.className = 'box';

            const header = document.createElement('div');
            header.className = 'header';
            const headerTitle = document.createElement('h2');
            headerTitle.textContent = this.modalTitle;

            const btnClose = document.createElement('div');
            btnClose.className = 'btnClose';
            btnClose.innerHTML = '<div>Close</div>';

            header.appendChild(headerTitle);
            header.appendChild(btnClose);

            const content = document.createElement('div');
            content.className = 'content';

            this.modalForm.feedbackError = document.createElement('div');
            this.modalForm.feedbackError.className = 'errorFeedback';
            content.appendChild(this.modalForm.feedbackError);
            this.modalForm.feedbackSuccess = document.createElement('div');
            this.modalForm.feedbackSuccess.className = 'successFeedback';
            content.appendChild(this.modalForm.feedbackSuccess);

            /*
             * MANUAL LINK
             */
            const labelLink = document.createElement('label');
            labelLink.textContent = 'Recover Link';
            const brippoRecoverLinkContainer = document.createElement('div');
            brippoRecoverLinkContainer.className = 'brippoRecoverLink';
            this.modalForm.recoverLink = document.createElement('a');
            content.appendChild(labelLink);
            content.appendChild(brippoRecoverLinkContainer);
            brippoRecoverLinkContainer.appendChild(this.modalForm.recoverLink);

            /*
             * SEND EMAIL CHECKBOX
             */
            const checkboxEmailContainer = document.createElement('div');
            checkboxEmailContainer.className = 'brippoRecoverOrderModal_checkboxContainer';
            content.appendChild(checkboxEmailContainer);
            this.modalForm.sendEmailCheckbox = document.createElement('input');
            this.modalForm.sendEmailCheckbox.id = 'brippoRecoverOrderModal_checkboxSendEmail';
            this.modalForm.sendEmailCheckbox.type = 'checkbox';
            this.modalForm.sendEmailCheckbox.onchange = this.onChangeInputModalRecoverOrder;
            checkboxEmailContainer.appendChild(this.modalForm.sendEmailCheckbox);
            const labelSendEmailCheckbox = document.createElement('label');
            labelSendEmailCheckbox.htmlFor = 'brippoRecoverOrderModal_checkboxSendEmail';
            labelSendEmailCheckbox.textContent = 'Send Email';
            checkboxEmailContainer.appendChild(labelSendEmailCheckbox);
            this.modalForm.inputCustomerEmail = document.createElement('input');
            this.modalForm.inputCustomerEmail.id = 'brippoRecoverOrderModal_customerEmail';
            this.modalForm.inputCustomerEmail.type = 'text';
            this.modalForm.inputCustomerEmail.placeholder = 'Customer email';
            content.appendChild(this.modalForm.inputCustomerEmail);

            /*
             * EMAIL MESSAGE
             */
            const labelMessage = document.createElement('label');
            labelMessage.htmlFor = 'brippoRecoverOrderModal_message';
            labelMessage.textContent = 'Email Message';
            this.modalForm.inputMessage = document.createElement('textarea');
            this.modalForm.inputMessage.id = 'brippoRecoverOrderModal_message';
            content.appendChild(labelMessage);
            content.appendChild(this.modalForm.inputMessage);

            /*
             * SEND MESSAGE CHECKBOX
             */
            const checkboxSMSContainer = document.createElement('div');
            checkboxSMSContainer.className = 'brippoRecoverOrderModal_checkboxContainer';
            content.appendChild(checkboxSMSContainer);
            this.modalForm.sendSMSCheckbox = document.createElement('input');
            this.modalForm.sendSMSCheckbox.id = 'brippoRecoverOrderModal_checkboxSendSMS';
            this.modalForm.sendSMSCheckbox.type = 'checkbox';
            this.modalForm.sendSMSCheckbox.onchange = this.onChangeInputModalRecoverOrder;
            checkboxSMSContainer.appendChild(this.modalForm.sendSMSCheckbox);
            const labelSendSMSCheckbox = document.createElement('label');
            labelSendSMSCheckbox.htmlFor = 'brippoRecoverOrderModal_checkboxSendSMS';
            labelSendSMSCheckbox.textContent = 'Send SMS/WhatsApp';
            checkboxSMSContainer.appendChild(labelSendSMSCheckbox);

            const messagePhoneContainer = document.createElement('div');
            messagePhoneContainer.className = 'brippoRecoverOrderModal_messagePhoneContainer';
            content.appendChild(messagePhoneContainer);

            /*
             * MESSAGE TYPE
             */
            const labelMessagingTypeSelect = document.createElement('label');
            labelMessagingTypeSelect.htmlFor = 'brippoRecoverOrderModal_messageType';
            labelMessagingTypeSelect.textContent = 'Message Type';
            this.modalForm.messagingTypeSelect = document.createElement('select');
            this.modalForm.messagingTypeSelect.id = 'brippoRecoverOrderModal_messageType';
            const messagingTypeSelectOption1 = document.createElement('option');
            messagingTypeSelectOption1.value = 'sms';
            messagingTypeSelectOption1.innerHTML = 'SMS';
            const messagingTypeSelectOption2 = document.createElement('option');
            messagingTypeSelectOption2.value = 'whatsapp';
            messagingTypeSelectOption2.innerHTML = 'WhatsApp';
            const messagingTypeSelectOption3 = document.createElement('option');
            messagingTypeSelectOption3.value = 'sms_whatsapp';
            messagingTypeSelectOption3.innerHTML = 'SMS and WhatsApp';
            messagePhoneContainer.appendChild(this.modalForm.messagingTypeSelect);
            this.modalForm.messagingTypeSelect.appendChild(messagingTypeSelectOption1);
            this.modalForm.messagingTypeSelect.appendChild(messagingTypeSelectOption2);
            this.modalForm.messagingTypeSelect.appendChild(messagingTypeSelectOption3);

            /*
             * CUSTOMER PHONE
             */
            this.modalForm.inputCustomerPhone = document.createElement('input');
            this.modalForm.inputCustomerPhone.id = 'brippoRecoverOrderModal_customerPhone';
            this.modalForm.inputCustomerPhone.type = 'text';
            this.modalForm.inputCustomerPhone.placeholder = 'Customer phone';
            messagePhoneContainer.appendChild(this.modalForm.inputCustomerPhone);

            const labelMessageSMS = document.createElement('label');
            labelMessageSMS.htmlFor = 'brippoRecoverOrderModal_messageSMS';
            labelMessageSMS.textContent = 'SMS/WhatsApp Message';
            this.modalForm.inputMessageSMS = document.createElement('textarea');
            this.modalForm.inputMessageSMS.id = 'brippoRecoverOrderModal_messageSMS';
            content.appendChild(labelMessageSMS);
            content.appendChild(this.modalForm.inputMessageSMS);

            const footer = document.createElement('div');
            footer.className = 'footer';

            this.modalForm.buttonSubmit = document.createElement('button');
            this.modalForm.buttonSubmit.className = 'button modalPrimaryBrippo';
            this.modalForm.buttonSubmit.type = 'button';
            this.modalForm.buttonSubmit.textContent = 'Send to Customer';
            footer.appendChild(this.modalForm.buttonSubmit);

            box.appendChild(header);
            box.appendChild(content);
            box.appendChild(footer);
            this.modal.appendChild(box);
            document.body.appendChild(this.modal);

            btnClose.addEventListener('click', () => {
                this.close();
            });

            this.modalForm.buttonSubmit.addEventListener('click', () => {
                this.submit().then();
            });
        },
        onChangeInputModalRecoverOrder() {
            BrippoAdmin.recoverOrder.modalForm.inputCustomerEmail.disabled = !BrippoAdmin.recoverOrder.modalForm.sendEmailCheckbox.checked;
            BrippoAdmin.recoverOrder.modalForm.inputMessage.disabled = !BrippoAdmin.recoverOrder.modalForm.sendEmailCheckbox.checked;

            BrippoAdmin.recoverOrder.modalForm.inputCustomerPhone.disabled = !BrippoAdmin.recoverOrder.modalForm.sendSMSCheckbox.checked;
            BrippoAdmin.recoverOrder.modalForm.inputMessageSMS.disabled = !BrippoAdmin.recoverOrder.modalForm.sendSMSCheckbox.checked;
            BrippoAdmin.recoverOrder.modalForm.messagingTypeSelect.disabled = !BrippoAdmin.recoverOrder.modalForm.sendSMSCheckbox.checked;

            BrippoAdmin.recoverOrder.modalForm.buttonSubmit.disabled = !BrippoAdmin.recoverOrder.modalForm.sendSMSCheckbox.checked && !BrippoAdmin.recoverOrder.modalForm.sendEmailCheckbox.checked;
        },
        getModal() {
            if (!this.modal) {
                this.initModal();
            }

            return this.modal;
        },
        populateModal(orderIncrementId, customerEmail, customerName, storeName, customerPhone, recoverLink) {
            this.initModal();
            this.modalForm.feedbackSuccess.style.display = 'none';
            this.modalForm.feedbackError.style.display = 'none';

            this.modalForm.sendEmailCheckbox.checked = true;
            this.modalForm.inputCustomerEmail.value = customerEmail;
            this.modalForm.inputMessage.innerHTML = BrippoAdmin.config?.recoverOrder.defaultMessage
                .replace('[name]', customerName)
                .replace('[store]', (storeName ?? BrippoAdmin.config?.storeName));
            this.modalForm.recoverLink.href
                = this.modalForm.recoverLink.innerHTML
                = recoverLink;
            this.modalForm.recoverLink.target = '_blank';

            if (customerPhone && customerPhone !== '') {
                this.modalForm.sendSMSCheckbox.checked = true;
                this.modalForm.inputCustomerPhone.value = customerPhone;
                this.modalForm.inputMessageSMS.innerHTML = BrippoAdmin.config?.recoverOrder.defaultMessageSMS
                    .replace('[store]', (storeName ?? BrippoAdmin.config?.storeName));
            }

            this.onChangeInputModalRecoverOrder();
        },
        show(orderId, orderIncrementId, customerEmail, customerName, storeName, customerPhone, recoverLink) {
            if (BrippoAdmin.wasInitialized) {
                if (BrippoAdmin.config?.isServiceReady) {
                    this.orderId = orderId;
                    this.populateModal(orderIncrementId, customerEmail, customerName, storeName, customerPhone, recoverLink);
                    this.getModal().style.display = 'flex';
                } else {
                    BrippoAdmin.onboardingRequired.show(this.modalTitle);
                }
            }
        },
        close() {
            this.getModal().style.display = 'none';
        },
        async submit() {
            this.modalForm.feedbackSuccess.style.display = 'none';
            this.modalForm.feedbackError.style.display = 'none';
            BrippoAdmin.showVeilThrobber();
            await fetch(
                BrippoAdmin.config?.recoverOrder.sendRecoverOrderNotification,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        orderId: this.orderId,
                        customerEmail: this.modalForm.inputCustomerEmail.value,
                        emailMessage: this.modalForm.inputMessage.value,
                        sendEmail: this.modalForm.sendEmailCheckbox.checked,
                        sendSMSWhatsApp: this.modalForm.sendSMSCheckbox.checked,
                        messageType: this.modalForm.messagingTypeSelect.value,
                        customerPhone: this.modalForm.inputCustomerPhone.value,
                        phoneMessage: this.modalForm.inputMessageSMS.value,
                    }),
                headers: {'Content-Type': 'application/json'}
                }
            )
                .then(response => {
                    if (!response.ok) {
                        console.log(response);
                        throw new Error('There was a problem while fetching Brippo sendRecoverOrderEmail.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.valid === 1) {
                        this.modalForm.feedbackSuccess.innerHTML = 'Notification/s were sent'
                        this.modalForm.feedbackSuccess.style.display = 'block';
                    } else {
                        console.log('There was a problem while fetching Brippo sendRecoverOrderEmail:', data?.message);
                        this.modalForm.feedbackError.innerHTML = data?.message;
                        this.modalForm.feedbackError.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('There was a problem while fetching Brippo sendRecoverOrderEmail:', error);
                    this.modalForm.feedbackError.innerHTML = error;
                    this.modalForm.feedbackError.style.display = 'block';
                });
            BrippoAdmin.hideVeilThrobber();
        }
    },
    processPaymentInTerminal: {
        modalTitle: 'Process Payment in Terminal',
        modal: null,
        modalForm: {
            actionStatus: null,
            contentThrobber: null,
            terminalsList: null,
            motoOptionContainer: null,
            motoOptionInput: null,
            buttonSubmit: null,
            feedbackSuccess: null,
            feedbackError: null,
        },
        terminalLocations: null,
        intervalIdTerminalActionCheck: null,
        currentPaymentIntentId: null,
        currentOrderId: null,
        initModal() {
            if (this.modal) {
                return;
            }

            this.modal = document.createElement('div');
            this.modal.className = 'brippoAdminModal';
            this.modal.id = 'brippoProcessPaymentInTerminalModal';

            const box = document.createElement('div');
            box.className = 'box';

            const header = document.createElement('div');
            header.className = 'header';
            const headerTitle = document.createElement('h2');
            headerTitle.textContent = this.modalTitle;

            const btnClose = document.createElement('div');
            btnClose.className = 'btnClose';
            btnClose.innerHTML = '<div>Close</div>';

            header.appendChild(headerTitle);
            header.appendChild(btnClose);

            const content = document.createElement('div');
            content.className = 'content';

            this.modalForm.feedbackError = document.createElement('div');
            this.modalForm.feedbackError.className = 'errorFeedback';
            content.appendChild(this.modalForm.feedbackError);
            this.modalForm.feedbackSuccess = document.createElement('div');
            this.modalForm.feedbackSuccess.className = 'successFeedback';
            content.appendChild(this.modalForm.feedbackSuccess);

            this.modalForm.contentThrobber = document.createElement('div');
            this.modalForm.contentThrobber.innerHTML = '<div class="throbber"></div>';
            this.modalForm.contentThrobber.className = 'brippoThrobberModal';
            content.appendChild(this.modalForm.contentThrobber);

            const subtitleThrobber = document.createElement('p');
            subtitleThrobber.innerHTML = 'Fetching available terminals...';
            subtitleThrobber.className = 'brippoSubtitleModalThrobber';
            this.modalForm.contentThrobber.appendChild(subtitleThrobber);

            this.modalForm.terminalsList = document.createElement('ul');
            content.appendChild(this.modalForm.terminalsList);

            this.modalForm.motoOptionContainer = document.createElement('div');
            this.modalForm.motoOptionContainer.className = 'brippoTerminalBackendCardInputMethodContainer';
            this.modalForm.motoOptionInput = document.createElement('select');
            const motoOptionLabel = document.createElement('label');
            motoOptionLabel.innerHTML = 'Card input method';
            motoOptionLabel.htmlFor = this.modalForm.motoOptionInput.id = 'brippoTerminalBackendCardInputMethod';
            this.modalForm.motoOptionInput.innerHTML = '<option value="card_present" selected>Card present (chip/contactless)</option>'
                + '<option value="moto">Typed input (MO/TO)</option>';
            this.modalForm.motoOptionContainer.appendChild(motoOptionLabel);
            this.modalForm.motoOptionContainer.appendChild(this.modalForm.motoOptionInput);
            content.appendChild(this.modalForm.motoOptionContainer);

            this.modalForm.actionStatus = document.createElement('div');
            content.appendChild(this.modalForm.actionStatus);

            const footer = document.createElement('div');
            footer.className = 'footer';

            this.modalForm.buttonSubmit = document.createElement('button');
            this.modalForm.buttonSubmit.className = 'button modalPrimaryBrippo';
            this.modalForm.buttonSubmit.type = 'button';
            this.modalForm.buttonSubmit.textContent = 'Send to Terminal';
            footer.appendChild(this.modalForm.buttonSubmit);

            box.appendChild(header);
            box.appendChild(content);
            box.appendChild(footer);
            this.modal.appendChild(box);
            document.body.appendChild(this.modal);

            btnClose.addEventListener('click', () => {
                this.close();
            });

            this.modalForm.buttonSubmit.addEventListener('click', () => {
                this.submit().then();
            });
        },
        getModal() {
            if (!this.modal) {
                this.initModal();
            }

            return this.modal;
        },
        async populateModal() {
            this.initModal();
            this.modalForm.feedbackSuccess.style.display = 'none';
            this.modalForm.feedbackError.style.display = 'none';
            this.modalForm.terminalsList.innerHTML = '<strong>Select an available terminal for processing:</strong>';
            this.modalForm.terminalsList.style.display = 'none';
            this.modalForm.actionStatus.style.display = 'none';
            this.modalForm.motoOptionContainer.style.display = 'none';
            this.modalForm.contentThrobber.style.display = 'block';
            this.modalForm.buttonSubmit.style.display = 'inline-block';
            this.modalForm.buttonSubmit.disabled = true;

            const showNoTerminalsError = () => {
                this.modalForm.feedbackError.innerHTML = 'No terminals are available. Please check your paired terminals <a href="https://dashboard.brippo.com/accounts/dashboard#terminals" target="_blank">here</a>.';
                this.modalForm.feedbackError.style.display = 'block';
            }

            const {error, terminalLocations} = await this.getTerminalLocations();
            if (error) {
                this.modalForm.feedbackError.innerHTML = error;
                this.modalForm.feedbackError.style.display = 'block';
            } else if (!terminalLocations || terminalLocations.length === 0) {
                showNoTerminalsError();
            } else {
                let availableReaders = 0;
                for (const location of terminalLocations) {
                    if (location.readersAssigned && location.readersAssigned.length > 0) {
                        for (const reader of location.readersAssigned) {
                            availableReaders++;
                            const inputId = 'brippoTerminalSelection_' + reader.id;
                            this.modalForm.terminalsList.innerHTML +=
                                '<li>'
                                + '<input type="radio" name="brippoTerminalSelection" id="' + inputId + '"'
                                + (availableReaders === 1 ? ' checked' : '') + '>'
                                + '<label for="' + inputId + '">'
                                + (location.display_name ? location.display_name + ' - ' : '')
                                + (reader.label && reader.label !== '' ? reader.label : reader.serial_number)
                                + '</label></li>';
                        }
                    }
                }
                if (availableReaders > 0) {
                    this.modalForm.terminalsList.style.display = 'block';
                    this.modalForm.buttonSubmit.disabled = false;
                    this.modalForm.motoOptionContainer.style.display = 'block';
                } else {
                    showNoTerminalsError();
                }
            }
            this.modalForm.contentThrobber.style.display = 'none';
        },
        show() {
            if (BrippoAdmin.wasInitialized) {
                this.populateModal().then();
                this.getModal().style.display = 'flex';
                this.currentPaymentIntentId = document.getElementById("brippoPaymentIntentId")?.value;
                this.currentOrderId = document.getElementById("brippoOrderId")?.value;
            }
        },
        close() {
            this.getModal().style.display = 'none';
            this.cancelTerminalActionStatusCheck();
        },
        async submit() {
            this.modalForm.feedbackSuccess.style.display = 'none';
            this.modalForm.feedbackError.style.display = 'none';
            BrippoAdmin.showVeilThrobber();

            const selectedTerminalRadioButton = document.querySelector('input[name="brippoTerminalSelection"]:checked');
            const readerId = selectedTerminalRadioButton?.id.replace('brippoTerminalSelection_', '');
            const cardInputMethod = this.modalForm.motoOptionInput.value;

            if (!this.currentPaymentIntentId || this.currentPaymentIntentId === '') {
                this.modalForm.feedbackError.innerHTML = 'Payment intent ID not found';
                this.modalForm.feedbackError.style.display = 'block';
                return;
            }

            if (!readerId || readerId === '') {
                this.modalForm.feedbackError.innerHTML = 'No terminal was selected';
                this.modalForm.feedbackError.style.display = 'block';
                return;
            }

            await fetch(
                BrippoAdmin.config?.terminals.processPaymentInTerminal,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        readerId: readerId,
                        paymentIntentId: this.currentPaymentIntentId,
                        cardInputMethod: cardInputMethod
                    }),
                    headers: {
                        'Content-Type': 'application/json'
                    }
                }
            )
                .then(response => {
                    if (!response.ok) {
                        console.log(response);
                        throw new Error('There was a problem while trying to process payment in terminal.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.valid === 1) {
                        this.modalForm.feedbackSuccess.innerHTML = 'Payment was sent to the terminal for processing.';
                        this.modalForm.feedbackSuccess.style.display = 'block';
                        this.modalForm.terminalsList.style.display = 'none';
                        this.modalForm.motoOptionContainer.style.display = 'none';
                        this.modalForm.buttonSubmit.style.display = 'none';
                        this.updateTerminalActionStatus(readerId);
                        this.intervalIdTerminalActionCheck = setInterval(() => {
                            this.updateTerminalActionStatus(readerId);
                        }, 15000);
                    } else {
                        console.log('There was a problem while fetching Brippo processPaymentInTerminal:', data?.message);
                        this.modalForm.feedbackError.innerHTML = data?.message;
                        this.modalForm.feedbackError.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.log('There was a problem while fetching Brippo processPaymentInTerminal:', error);
                    this.modalForm.feedbackError.innerHTML = error;
                    this.modalForm.feedbackError.style.display = 'block';
                });
            BrippoAdmin.hideVeilThrobber();
        },
        async getTerminalLocations() {
            let error, terminalLocations;
            await fetch(BrippoAdmin.config?.terminals.getTerminals, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
            })
                .then(response => {
                    if (!response.ok) {
                        error = 'There was a problem while fetching Brippo terminal locations.'
                        throw new Error(error);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.valid === 1) {
                        terminalLocations = data.locations;
                    } else {
                        error = data?.message;
                    }
                })
                .catch(error => {
                    console.log('There was a problem while fetching Brippo terminal locations:', error);
                });

            return {error, terminalLocations};
        },
        async updateTerminalActionStatus(terminalId) {
            if (this.modalForm.actionStatus.style.display === 'none') {
                this.modalForm.actionStatus.innerHTML = '<p class="brippoTerminalActionStatus">Retrieving processing status...</p>';
                this.modalForm.actionStatus.style.display = 'block';
            }

            const {error, terminal} = await this.getTerminalStatus(terminalId);
            if (error) {
                this.modalForm.feedbackError.innerHTML = error;
                this.modalForm.feedbackError.style.display = 'block';
                this.modalForm.actionStatus.style.display = 'none';
                return;
            }
            const actionStatus = terminal.action?.status;
            let statusTag = '<span class="brippoTag neutral">UNKNOWN</span>';
            switch (actionStatus) {
                case 'in_progress':
                    statusTag = '<span class="brippoTag inTransit">IN PROGRESS <span class="brippoInlineThrobber"><span class="throbber"></span></span></span>';
                    break;
                case 'failed':
                    statusTag = '<span class="brippoTag danger">FAILED</span>';
                    break;
                case 'succeeded':
                    statusTag = '<span class="brippoTag success">SUCCEEDED</span>';
                    break;
            }

            this.modalForm.actionStatus.innerHTML = '<p class="brippoTerminalActionStatus">Processing status: '
                + statusTag + '</p>';
            this.modalForm.actionStatus.style.display = 'block';

            if (actionStatus === 'succeeded') {
                BrippoAdmin.showVeilThrobber();
                this.cancelTerminalActionStatusCheck();
                await this.onPaymentSucceeded();
                location.reload();
            } else if (actionStatus === 'failed') {
                this.cancelTerminalActionStatusCheck();
            }
        },
        async getTerminalStatus(terminalId) {
            let error, terminal;
            await fetch(BrippoAdmin.config?.terminals.getTerminalStatus + 'terminalId/' + terminalId, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
            })
                .then(response => {
                    if (!response.ok) {
                        error = 'There was a problem while fetching Brippo Terminal status.'
                        throw new Error(error);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.valid === 1) {
                        terminal = data.data;
                    } else {
                        error = data?.message;
                    }
                })
                .catch(error => {
                    console.log('There was a problem while fetching Brippo Terminal status:', error);
                });

            return {error, terminal};
        },
        cancelTerminalActionStatusCheck() {
            if (this.intervalIdTerminalActionCheck) {
                clearInterval(this.intervalIdTerminalActionCheck);
                this.intervalIdTerminalActionCheck = null;
            }
        },
        async onPaymentSucceeded() {
            await fetch(BrippoAdmin.config?.terminals.actionCompleted, {
                method: 'POST',
                body: JSON.stringify({
                    orderId: this.currentOrderId
                }),
                headers: {
                    'Content-Type': 'application/json'
                },
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('There was a problem while fetching Brippo action completed.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.valid === 1) {
                        console.log('Action completed successfully')
                    } else {
                        throw new Error(data?.message);
                    }
                })
                .catch(error => {
                    console.error('There was a problem while fetching Brippo action completed:', error);
                });
        },
        async sendReceipt() {
            BrippoAdmin.showVeilThrobber();
            await fetch(
                BrippoAdmin.config?.terminals.sendReceipt,
                {
                    method: 'POST',
                    body: JSON.stringify({
                        orderId: document.getElementById("brippoOrderId")?.value,
                        paymentIntentId: document.getElementById("brippoPaymentIntentId")?.value
                    }),
                    headers: {'Content-Type': 'application/json'}
                }
            )
                .then(response => {
                    if (!response.ok) {
                        console.log(response);
                        BrippoAdmin.hideVeilThrobber();
                        throw new Error('There was a problem while fetching Brippo sendReceipt.');
                    }
                    location.reload();
                })
                .catch(error => {
                    console.error('There was a problem while fetching Brippo sendReceipt:', error);
                });
        }
    },
    onboardingRequired: {
        modal: null,
        modalForm: {
            headerTitle: null,
            onboardingRequiredParagraph: null,
            buttonSubmit: null,
        },
        initModal() {
            if (this.modal) {
                return;
            }

            this.modal = document.createElement('div');
            this.modal.className = 'brippoAdminModal';
            this.modal.id = 'brippoOnboardingRequiredModal';

            const box = document.createElement('div');
            box.className = 'box';

            const header = document.createElement('div');
            header.className = 'header';
            this.modalForm.headerTitle = document.createElement('h2');

            const btnClose = document.createElement('div');
            btnClose.className = 'btnClose';
            btnClose.innerHTML = '<div>Close</div>';

            header.appendChild(this.modalForm.headerTitle);
            header.appendChild(btnClose);

            const content = document.createElement('div');
            content.className = 'content';

            this.modalForm.onboardingRequiredParagraph = document.createElement('p');
            this.modalForm.onboardingRequiredParagraph.innerHTML = 'Brippo\'s <i>Recovery Checkout</i> feature requires you to set up a Brippo account. The Brippo account is <b>free to sign up</b>, Brippo transaction costs apply.';
            content.appendChild(this.modalForm.onboardingRequiredParagraph);

            const setupStepsList = document.createElement('ul');
            setupStepsList.className = 'stepsToSetupBrippo';
            const setupStepsListItem1 = document.createElement('li');
            setupStepsListItem1.innerHTML = '<span class="stepNumber">1</span><b>Register a new User Account</b> in Brippo portal using your email address. <span class="timeEstimation">Time estimated 1-2 min</span>';
            setupStepsList.appendChild(setupStepsListItem1);
            const setupStepsListItem2 = document.createElement('li');
            setupStepsListItem2.innerHTML = '<span class="stepNumber">2</span><b>Create a new Connected Account</b> for your business entity. Your business details will be requested along a bank account for your payouts. <span class="timeEstimation">Time estimated 5-8 min</span>';
            setupStepsList.appendChild(setupStepsListItem2);
            content.appendChild(setupStepsList);

            const footer = document.createElement('div');
            footer.className = 'footer';

            this.modalForm.buttonSubmit = document.createElement('button');
            this.modalForm.buttonSubmit.className = 'button modalPrimaryBrippo';
            this.modalForm.buttonSubmit.type = 'button';
            this.modalForm.buttonSubmit.textContent = 'Setup Brippo';
            footer.appendChild(this.modalForm.buttonSubmit);

            box.appendChild(header);
            box.appendChild(content);
            box.appendChild(footer);
            this.modal.appendChild(box);
            document.body.appendChild(this.modal);

            btnClose.addEventListener('click', () => {
                this.close();
            });

            this.modalForm.buttonSubmit.addEventListener('click', () => {
                this.submit().then();
            });
        },
        getModal() {
            if (!this.modal) {
                this.initModal();
            }

            return this.modal;
        },
        populateModal(title, text = '') {
            this.initModal();
            this.modalForm.headerTitle.innerHTML = title;

            if (text !== '') {
                this.modalForm.onboardingRequiredParagraph.innerHTML = text;
            }
        },
        show(title) {
            this.populateModal(title);
            this.getModal().style.display = 'flex';
        },
        showWithText(title, text) {
            this.populateModal(title, text);
            this.getModal().style.display = 'flex';
        },
        close() {
            this.getModal().style.display = 'none';
        },
        async submit() {
            const returnUrl = BrippoAdmin.config?.onboarding.responseUrl
                + 'redirect/' + encodeURIComponent(btoa(window.location.href));

            window.location.href = BrippoAdmin.config?.onboarding.goToUrl
                + 'live'
                + '/magento/'
                + encodeURIComponent(returnUrl);
        },
    },
    async initialize() {
        if (!this.wasInitialized) {
            this.formKey =
                window.FORM_KEY ||
                document.querySelector('input[name="form_key"]')?.value ||
                (document.cookie.match(/(?:^|;\s*)form_key=([^;]+)/)?.[1]);

            if (!this.formKey) {
                console.warn('Form key not found');
            }

            this.config = await this.getConfiguration();
            this.wasInitialized = this.config !== null;

            /*
             * Trigger Brippo Recover
             */
            const brippoRecoverOnload = this.extractVarFromUrl(window.location.href, 'brippoRecoverOnload');
            if (brippoRecoverOnload) {
                document.getElementById('brippo_recover_order')?.click();
            }

            /*
             * Trigger Process in Terminal
             */
            const brippoOnLoadAction = document.getElementById("brippoOnLoadAction");
            if (brippoOnLoadAction) {
                if (brippoOnLoadAction.value === 'processPaymentInTerminal') {
                    this.processPaymentInTerminal.show();
                }
            }
        }
    },
    async getConfiguration() {
        let config;

        if (typeof brippoControllerUrlConfiguration !== 'undefined') {
            try {
                const res = await fetch(brippoControllerUrlConfiguration, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ form_key: this.formKey })
                });

                if (!res.ok) {
                    console.error(res);
                    throw new Error('There was a problem while fetching Brippo configuration.');
                }

                const data = await res.json();

                if (data && data.valid === 1) {
                    config = data;
                } else {
                    console.log('There was a problem while fetching Brippo configuration:', data?.message);
                }
            } catch (error) {
                console.log('There was a problem while fetching Brippo configuration:', error);
            }
        }

        return config;
    },
    waitForBody(callback) {
        if (document.body) {
            callback();
        } else {
            requestAnimationFrame(() => this.waitForBody(callback));
        }
    },
    generateThrobber() {
        const throbberElement = document.createElement('div');
        throbberElement.innerHTML = '<div class="throbber"></div>';
        throbberElement.id = 'brippoPaymentsThrobber';
        throbberElement.className = 'brippoThrobberVeil';
        document.body.appendChild(throbberElement);
    },
    showVeilThrobber() {
        const throbber = document.getElementById('brippoPaymentsThrobber');
        if (throbber) {
            throbber.style.display = 'block';
        }
    },
    hideVeilThrobber() {
        const throbber = document.getElementById('brippoPaymentsThrobber');
        if (throbber) {
            throbber.style.display = 'none';
        }
    },
    extractVarFromUrl(url, key) {
        const segments = url.split('/');
        const index = segments.indexOf(key);
        if (index !== -1 && segments.length > index + 1) {
            return segments[index + 1];
        }
        return null;
    },
    async getOrderLog(orderId) {
        let logData;
        if (typeof brippoControllerUrlOrderLog !== 'undefined') {
            await fetch(brippoControllerUrlOrderLog + 'orderIncrementId/' + orderId, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
            })
                .then(response => {
                    if (!response.ok) {
                        console.log(response);
                        throw new Error('There was a problem while fetching Brippo payment log.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data && data.valid === 1) {
                        logData = data.data;
                    } else {
                        console.log('There was a problem while fetching Brippo payment log', data?.message);
                    }
                })
                .catch(error => {
                    console.log('There was a problem while fetching Brippo payment log', error);
                });
        }

        return { logData };
    },
    async invoiceOffline(orderId) {
        let response = { valid: 0, message: 'Unknown error' };

        BrippoAdmin.showVeilThrobber();

        await fetch(
            BrippoAdmin.config?.invoiceOffline?.url,
            {
                method: 'POST',
                body: JSON.stringify({
                    orderId: orderId
                }),
                headers: {'Content-Type': 'application/json'}
            }
        )
            .then(response => {
                if (!response.ok) {
                    console.log(response);
                    throw new Error('There was a problem while processing offline invoice.');
                }
                return response.json();
            })
            .then(data => {
                if (data && data.valid === 1) {
                    response = {
                        valid: 1,
                        message: data.message || 'Invoice processed successfully'
                    };
                } else {
                    console.log('There was a problem while processing offline invoice:', data?.message);
                    response = {
                        valid: 0,
                        message: data?.message || 'Failed to process invoice'
                    };
                }
            })
            .catch(error => {
                console.error('There was a problem while processing offline invoice:', error);
                response = {
                    valid: 0,
                    message: error.message || 'Network error occurred'
                };
            });

        BrippoAdmin.hideVeilThrobber();
        return response;
    },
}

document.addEventListener('DOMContentLoaded', function () {
    BrippoAdmin.initialize().then();
});

BrippoAdmin.waitForBody(() => {
    BrippoAdmin.generateThrobber();
});
