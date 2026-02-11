(function($) {
    'use strict';

    const BookingApp = {
        state: {
            step: 1,
            checkin: '',
            checkout: '',
            guests: 1,
            selectedRooms: [],
            selectedServices: [],
            guestData: {}
        },

        init: function() {
            this.bindEvents();
        },

        formatPrice: function(amount) {
            const formatted = parseFloat(amount).toFixed(2);
            if (resortData.currency.pos === 'before') {
                return `${resortData.currency.code} ${formatted}`;
            } else {
                return `${formatted} ${resortData.currency.code}`;
            }
        },

        bindEvents: function() {
            $(document).on('click', '#resort-search-btn', this.handleSearch.bind(this));
            $(document).on('click', '.select-room-btn', this.handleRoomSelection.bind(this));
            $(document).on('click', '.remove-room-btn', this.handleRoomRemoval.bind(this));
            $(document).on('click', '#resort-rooms-next', this.handleRoomsNext.bind(this));
            $(document).on('click', '#resort-services-next', this.handleServicesSelection.bind(this));
            $(document).on('submit', '#resort-guest-form', this.handleGuestForm.bind(this));
            $(document).on('click', '#resort-apply-coupon', this.handleCouponApply.bind(this));
            $(document).on('click', '#resort-apply-points', this.handlePointsApply.bind(this));
            $(document).on('click', '#resort-complete-booking', this.handleCompleteBooking.bind(this));

            // Review Modal
            $(document).on('click', '.show-review-form', (e) => {
                $('#review-booking-id').val($(e.currentTarget).data('booking'));
                $('#resort-review-modal').show();
            });
			$(document).on('click', '.show-modify-form', (e) => {
				$('#modify-booking-id').val($(e.currentTarget).data('booking'));
				$('#resort-modify-modal').show();
			});
			$(document).on('click', '.show-pay-modal', (e) => {
				$('#resort-dashboard-pay-now').data('booking', $(e.currentTarget).data('booking'));
				$('#resort-pay-modal').show();
			});
			$(document).on('click', '.show-service-request-form', (e) => {
				$('#service-request-booking-id').val($(e.currentTarget).data('booking'));
				$('#resort-service-request-modal').show();
			});
			$(document).on('click', '#resort-view-waiver-link', (e) => {
				e.preventDefault();
				const content = resortData.waiver_text + "\n\n" + resortData.terms_text;
				$('#resort-waiver-content-area').text(content);
				$('#resort-waiver-modal').show();
			});
            $(document).on('click', '.close-modal', () => {
				$('#resort-review-modal').hide();
				$('#resort-modify-modal').hide();
				$('#resort-pay-modal').hide();
				$('#resort-service-request-modal').hide();
				$('#resort-waiver-modal').hide();
			});
            $(document).on('submit', '#resort-review-form', this.handleReviewSubmit.bind(this));
			$(document).on('submit', '#resort-modify-form', this.handleModifySubmit.bind(this));
			$(document).on('click', '#resort-dashboard-pay-now', this.handleDashboardPay.bind(this));
			$(document).on('submit', '#resort-service-request-form', this.handleServiceRequestSubmit.bind(this));
			$(document).on('click', '.self-checkin-btn', this.handleSelfCheckin.bind(this));
			$(document).on('click', '.self-checkout-btn', this.handleSelfCheckout.bind(this));
        },

        goToStep: function(step) {
            this.state.step = step;
            $('.resort-step').hide();
            $(`#resort-step-${step}`).show();

            $('.resort-step-indicator .step').removeClass('active');
            $(`.resort-step-indicator .step[data-step="${step}"]`).addClass('active');
        },

        handleSearch: function() {
            const checkin = $('#resort-checkin').val();
            const checkout = $('#resort-checkout').val();
            const guests = $('#resort-guests').val();

            if (!checkin || !checkout) {
                alert('Please select both check-in and check-out dates.');
                return;
            }

            // Enforce Min/Max Nights (passed via localized data)
            const start = new Date(checkin);
            const today = new Date();
            today.setHours(0,0,0,0);

            if (resortData.rules && resortData.rules.book_ahead > 0) {
                const minStartDate = new Date(today);
                minStartDate.setDate(today.getDate() + parseInt(resortData.rules.book_ahead));
                if (start < minStartDate) {
                    alert(`Reservations must be made at least ${resortData.rules.book_ahead} days in advance.`);
                    return;
                }
            }

            const end = new Date(checkout);
            const diffTime = Math.abs(end - start);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (resortData.rules) {
                if (diffDays < resortData.rules.min_nights) {
                    alert(`Minimum stay is ${resortData.rules.min_nights} nights.`);
                    return;
                }
                if (diffDays > resortData.rules.max_nights) {
                    alert(`Maximum stay is ${resortData.rules.max_nights} nights.`);
                    return;
                }
            }

            this.state.checkin = checkin;
            this.state.checkout = checkout;
            this.state.guests = guests;

            const priceFilter = $('#resort-filter-price').val();
            const typeFilter = $('#resort-filter-type').val();

            $.ajax({
                url: `${resortData.api_url}/availability`,
                data: {
                    checkin,
                    checkout,
                    guests,
                    price_range: priceFilter,
                    type: typeFilter
                },
                method: 'GET',
                success: (rooms) => {
                    this.renderRooms(rooms);
                    this.goToStep(2);
                },
                error: () => {
                    alert('Error searching for rooms. Please try again.');
                }
            });
        },

        renderRooms: function(rooms) {
            const $grid = $('#resort-rooms-grid');
            const template = document.getElementById('resort-room-card-template');
            $grid.empty();

            if (rooms.length === 0) {
                $grid.append('<p>No rooms available for these dates.</p>');
                return;
            }

            rooms.forEach(room => {
                const clone = template.content.cloneNode(true);
                $(clone).find('.room-title').text(room.title);
                $(clone).find('.room-description').text(room.description);
                $(clone).find('.room-capacity').text(`Capacity: ${room.capacity}`);
                $(clone).find('.room-price').text(`Total: ${this.formatPrice(room.price)}`);
                $(clone).find('img').attr('src', room.image || '');
                $(clone).find('.select-room-btn').data('room', room);
                $grid.append(clone);
            });
        },

        handleRoomSelection: function(e) {
            const item = $(e.currentTarget).data('room');

            if (item.type === 'package') {
                // Packages are special: they might represent multiple things but for now we treat as one choice that clears others if desired
                // Or just add it as a "Room" with pre-selected services.
                // Requirement said "Allow multiple rooms per booking".
                // If they select a package, we'll add the base room and pre-fill services.

                const room = {
                    id: item.room_id,
                    title: item.title,
                    price: item.price, // Use package price for this "room"
                    is_package: true,
                    package_id: item.id
                };

                if (this.state.selectedRooms.find(r => r.id === room.id)) {
                    alert('The base room for this package is already in your selection.');
                    return;
                }

                this.state.selectedRooms.push(room);

                // Pre-select services from package
                if (item.services) {
                    item.services.forEach(s_id => {
                        if (!this.state.selectedServices.find(s => s.id == s_id)) {
                            this.state.selectedServices.push({ id: s_id, price: 0, title: 'Included in Package' });
                        }
                    });
                }
            } else {
                if (this.state.selectedRooms.find(r => r.id === item.id)) {
                    alert('This room is already in your selection.');
                    return;
                }
                this.state.selectedRooms.push(item);
            }

            this.updateSelectedRoomsUI();
        },

        handleRoomRemoval: function(e) {
            const roomId = $(e.currentTarget).data('id');
            this.state.selectedRooms = this.state.selectedRooms.filter(r => r.id !== roomId);
            this.updateSelectedRoomsUI();
        },

        updateSelectedRoomsUI: function() {
            const $container = $('#resort-selected-rooms-container');
            const $list = $('#resort-selected-list');
            $list.empty();

            if (this.state.selectedRooms.length > 0) {
                $container.show();
                this.state.selectedRooms.forEach(room => {
                    $list.append(`
                        <div style="display:flex; justify-content:space-between; align-items:center; background:#fff; padding:10px 15px; border-radius:8px; margin-bottom:10px;">
                            <span><strong>${room.title}</strong> - ${this.formatPrice(room.price)}</span>
                            <button type="button" class="remove-room-btn" data-id="${room.id}" style="background:none; border:none; color:#d63638; cursor:pointer;">&times; Remove</button>
                        </div>
                    `);
                });
            } else {
                $container.hide();
            }
        },

        handleRoomsNext: function() {
            if (this.state.selectedRooms.length === 0) {
                alert('Please select at least one room.');
                return;
            }
            this.fetchServices();
        },

        fetchServices: function() {
            $.ajax({
                url: `${resortData.api_url}/services`,
                method: 'GET',
                success: (services) => {
                    this.renderServices(services);
                    this.goToStep(3);
                }
            });
        },

        renderServices: function(services) {
            const $list = $('#resort-services-list');
            const template = document.getElementById('resort-service-item-template');
            $list.empty();

            if (services.length === 0) {
                $list.append('<p>No additional services available.</p>');
                return;
            }

            const start = new Date(this.state.checkin);
            const end = new Date(this.state.checkout);
            const nights = Math.ceil(Math.abs(end - start) / (1000 * 60 * 60 * 24));
            const guestCount = parseInt(this.state.guests);

            services.forEach(service => {
                const clone = template.content.cloneNode(true);
                const isPreSelected = this.state.selectedServices.find(s => s.id == service.id);

                let isRecommended = false;
                if (service.title.toLowerCase().includes('spa') && nights > 3) isRecommended = true;
                if (service.title.toLowerCase().includes('transfer') && guestCount > 2) isRecommended = true;
                if (service.title.toLowerCase().includes('dinner') && nights > 2) isRecommended = true;

                $(clone).find('.service-title').text(service.title);
                $(clone).find('.service-price').text(isPreSelected ? 'Included' : this.formatPrice(service.price));

                if (isRecommended && !isPreSelected) {
                    $(clone).find('.resort-service-item').css({
                        'border-color': 'var(--resort-coral)',
                        'background': 'var(--resort-light-teal)',
                        'position': 'relative'
                    }).prepend('<span class="resort-badge-recommended">RECOMMENDED FOR YOU</span>');
                }

                const $cb = $(clone).find('.resort-service-checkbox');
                $cb.val(service.id).data('price', service.price).data('title', service.title);

                if (isPreSelected) {
                    $cb.prop('checked', true).prop('disabled', true);
                }

                $list.append(clone);
            });
        },

        handleServicesSelection: function() {
            // Keep pre-selected services (from packages)
            const packageServices = this.state.selectedServices.filter(s => s.price === 0);
            this.state.selectedServices = [...packageServices];

            $('.resort-service-checkbox:checked:not(:disabled)').each((i, el) => {
                this.state.selectedServices.push({
                    id: $(el).val(),
                    price: $(el).data('price'),
                    title: $(el).data('title')
                });
            });
            this.goToStep(4);
        },

        handleGuestForm: function(e) {
            e.preventDefault();
            const formData = $(e.currentTarget).serializeArray();
            formData.forEach(item => {
                this.state.guestData[item.name] = item.value;
            });
            this.goToStep(5);
            this.renderSummary();
        },

        renderSummary: function() {
            const $summary = $('.booking-summary');
            let roomsHtml = '<p><strong>Accommodations:</strong><br>' + this.state.selectedRooms.map(r => `&bull; ${r.title} (${this.formatPrice(r.price)})`).join('<br>') + '</p>';

            let servicesHtml = '';
            let servicesTotal = 0;
            if (this.state.selectedServices.length > 0) {
                servicesHtml = '<p><strong>Extras:</strong> ' + this.state.selectedServices.map(s => s.title).join(', ') + '</p>';
                this.state.selectedServices.forEach(s => servicesTotal += parseFloat(s.price));
            }

            let roomsTotal = 0;
            this.state.selectedRooms.forEach(r => roomsTotal += parseFloat(r.price));

            const grandTotal = roomsTotal + servicesTotal;

            const depositPercent = parseInt(resortData.deposit_percent || 100);
            const depositAmount = (grandTotal * depositPercent) / 100;
            const balanceAmount = grandTotal - depositAmount;

            $summary.html(`
                ${roomsHtml}
                <p><strong>Dates:</strong> ${this.state.checkin} to ${this.state.checkout}</p>
                ${servicesHtml}
                <p><strong>Subtotal:</strong> ${this.formatPrice(grandTotal)}</p>
                <div id="discount-display" style="color: #d63638; display:none;"></div>
                <p><strong>Total Price:</strong> <span id="grand-total-display-container"></span></p>
                <div id="deposit-summary-display" style="margin: 10px 0; padding: 10px; background: #f0fdf4; border-radius: 8px; ${depositPercent < 100 ? '' : 'display:none;'}">
                    <p style="margin:0;"><strong>Due Now (${depositPercent}%):</strong> <span id="deposit-amount-display"></span></p>
                    <p style="margin:0; font-size: 0.9em; color: #666;"><strong>Remaining Balance:</strong> <span id="balance-amount-display"></span></p>
                </div>
                <p><strong>Guest:</strong> ${this.state.guestData.first_name} ${this.state.guestData.last_name} (${this.state.guestData.phone || 'No phone'})</p>
                ${this.state.guestData.special_requests ? `<p><strong>Requests:</strong> ${this.state.guestData.special_requests}</p>` : ''}
            `);
            $('#grand-total-display-container').text(this.formatPrice(grandTotal));
            $('#deposit-amount-display').text(this.formatPrice(depositAmount));
            $('#balance-amount-display').text(this.formatPrice(balanceAmount));
            this.state.finalTotal = grandTotal;

            // Adjust button text if only offline is available or all disabled
            const enabledMethodsCount = Object.values(resortData.payments).filter(v => v === '1').length;
            if (enabledMethodsCount === 0 || (enabledMethodsCount === 1 && resortData.payments.offline === '1')) {
                $('#resort-complete-booking').text('Confirm Booking');
            } else {
                $('#resort-complete-booking').text('Confirm & Pay');
            }
        },

        handlePointsApply: function() {
            const points = parseInt($('#resort-redeem-points').val());
            if (!points || points <= 0) return;

            const discount = points / 10; // 10 points = 1 PHP
            if (discount > this.state.finalTotal) {
                alert('Points discount cannot exceed the total amount.');
                return;
            }

            const newTotal = this.state.finalTotal - discount;
            $('#discount-display').append(`<br>Loyalty Discount: -${this.formatPrice(discount)}`).show();
            $('#grand-total-display-container').text(this.formatPrice(newTotal));

            this.state.pointsRedeemed = points;
            this.state.finalTotal = newTotal;
            this.updateDepositSummary();
            $('#points-message').text('Points applied!').css('color', 'green');
            $('#resort-apply-points').prop('disabled', true);
            $('#resort-redeem-points').prop('disabled', true);
        },

        handleCouponApply: function() {
            const code = $('#resort-coupon-code').val();
            if (!code) return;

            $.post(resortData.ajax_url, {
                action: 'resort_validate_coupon',
                nonce: resortData.nonce,
                code: code
            }, (res) => {
                if (res.success) {
                    let discount = 0;
                    if (res.data.type === 'fixed') {
                        discount = parseFloat(res.data.amount);
                    } else {
                        discount = (this.state.finalTotal * parseFloat(res.data.amount)) / 100;
                    }

                    const newTotal = Math.max(0, this.state.finalTotal - discount);
                    $('#discount-display').text(`Discount (${code}): -${this.formatPrice(discount)}`).show();
                    $('#grand-total-display-container').text(this.formatPrice(newTotal));
                    this.state.couponCode = code;
                    this.state.finalTotal = newTotal;
                    this.updateDepositSummary();
                    $('#coupon-message').text('Coupon applied!').css('color', 'green');
                } else {
                    $('#coupon-message').text(res.data.message).css('color', 'red');
                }
            });
        },

        updateDepositSummary: function() {
            const depositPercent = parseInt(resortData.deposit_percent || 100);
            const depositAmount = (this.state.finalTotal * depositPercent) / 100;
            const balanceAmount = this.state.finalTotal - depositAmount;

            $('#deposit-amount-display').text(this.formatPrice(depositAmount));
            $('#balance-amount-display').text(this.formatPrice(balanceAmount));
            if (depositPercent < 100) {
                $('#deposit-summary-display').show();
            }
        },

        handleCompleteBooking: function() {
            const paymentMethod = $('input[name="payment_method"]:checked').val();
            const ajaxAction = paymentMethod === 'stripe' ? 'resort_stripe_checkout' :
                               paymentMethod === 'paypal' ? 'resort_paypal_checkout' :
                               'resort_submit_booking';

            $.ajax({
                url: resortData.ajax_url,
                method: 'POST',
                data: {
                    action: 'resort_submit_booking',
                    nonce: resortData.nonce,
                    room_id: this.state.selectedRooms.map(r => r.id),
                    package_id: this.state.selectedRooms.find(r => r.is_package)?.package_id || '',
                    checkin: this.state.checkin,
                    checkout: this.state.checkout,
                    guests: this.state.guests,
                    guest_data: this.state.guestData,
                    services: this.state.selectedServices.map(s => s.id),
                    payment_method: paymentMethod,
                    coupon: this.state.couponCode || '',
                    points_redeemed: this.state.pointsRedeemed || 0,
                    final_total: this.state.finalTotal,
                    utm_source: new URLSearchParams(window.location.search).get('utm_source') || '',
                    utm_medium: new URLSearchParams(window.location.search).get('utm_medium') || '',
                    utm_campaign: new URLSearchParams(window.location.search).get('utm_campaign') || '',
                    referral_url: document.referrer || ''
                },
                success: (res) => {
                    if (res.success) {
                        const bookingId = res.data.booking_id;
                        // Now trigger specific payment if not offline
                        if (paymentMethod !== 'offline') {
                            this.processPayment(ajaxAction, bookingId);
                        } else {
                            this.showConfirmation(bookingId);
                        }
                    } else {
                        alert(res.data.message);
                    }
                }
            });
        },

        processPayment: function(action, bookingId) {
            $.ajax({
                url: resortData.ajax_url,
                method: 'POST',
                data: {
                    action: action,
                    nonce: resortData.nonce,
                    booking_id: bookingId
                },
                success: (res) => {
                    if (res.success) {
                        if (res.data && res.data.checkout_url) {
                            window.location.href = res.data.checkout_url;
                        } else {
                            this.showConfirmation(bookingId);
                        }
                    } else {
                        alert(res.data.message || 'Payment failed.');
                    }
                }
            });
        },

        showConfirmation: function(bookingId) {
            $('#resort-payment-screen').hide();
            $('#resort-confirmation-screen').show();
            $('#resort-conf-id').text(bookingId);
        },

		handleModifySubmit: function(e) {
			e.preventDefault();
			const form = $(e.currentTarget);
			const btn = form.find('button');
			const data = {
				action: 'resort_modify_request',
				nonce: resortData.nonce,
				booking_id: $('#modify-booking-id').val(),
				details: form.find('[name="request_details"]').val()
			};

			btn.prop('disabled', true).text('Sending...');

			$.post(resortData.ajax_url, data, function(res) {
				if (res.success) {
					alert(res.data.message);
					$('#resort-modify-modal').hide();
				} else {
					alert('Error sending request.');
					btn.prop('disabled', false).text('Send Request');
				}
			});
		},

		handleDashboardPay: function(e) {
			const btn = $(e.currentTarget);
			const bookingId = btn.data('booking');
			const method = $('input[name="dashboard_payment_method"]:checked').val();

			btn.prop('disabled', true).text('Initializing...');

			$.post(resortData.ajax_url, {
				action: 'resort_pay_balance',
				nonce: resortData.nonce,
				booking_id: bookingId,
				payment_method: method
			}, (res) => {
				if (res.success) {
					this.processPayment(res.data.ajax_action, bookingId);
				} else {
					alert(res.data.message);
					btn.prop('disabled', false).text('Pay Now');
				}
			});
		},

		handleSelfCheckout: function(e) {
			const btn = $(e.currentTarget);
			const bookingId = btn.data('booking');

			if (!confirm('Are you departing from the resort now? This will mark you as checked-out.')) return;

			btn.prop('disabled', true).text('Checking out...');

			$.post(resortData.ajax_url, {
				action: 'resort_self_checkout',
				nonce: resortData.nonce,
				booking_id: bookingId
			}, (res) => {
				if (res.success) {
					alert(res.data.message);
					window.location.reload();
				} else {
					alert(res.data.message);
					btn.prop('disabled', false).text('Self Check-out');
				}
			});
		},

		handleSelfCheckin: function(e) {
			const btn = $(e.currentTarget);
			const bookingId = btn.data('booking');

			if (!confirm('Are you arriving at the resort now? This will mark you as checked-in.')) return;

			btn.prop('disabled', true).text('Checking in...');

			$.post(resortData.ajax_url, {
				action: 'resort_self_checkin',
				nonce: resortData.nonce,
				booking_id: bookingId
			}, (res) => {
				if (res.success) {
					alert(res.data.message);
					window.location.reload();
				} else {
					alert(res.data.message);
					btn.prop('disabled', false).text('Self Check-in');
				}
			});
		},

		handleServiceRequestSubmit: function(e) {
			e.preventDefault();
			const form = $(e.currentTarget);
			const btn = form.find('button');
			const data = {
				action: 'resort_submit_service_request',
				nonce: resortData.nonce,
				booking_id: $('#service-request-booking-id').val(),
				request_type: form.find('[name="request_type"]').val(),
				request_details: form.find('[name="request_details"]').val()
			};

			btn.prop('disabled', true).text('Sending...');

			$.post(resortData.ajax_url, data, function(res) {
				if (res.success) {
					alert(res.data.message);
					$('#resort-service-request-modal').hide();
				} else {
					alert('Error sending request.');
					btn.prop('disabled', false).text('Send Request');
				}
			});
		},

        handleReviewSubmit: function(e) {
            e.preventDefault();
            const data = {
                action: 'resort_submit_review',
                nonce: resortData.nonce,
                booking_id: $('#review-booking-id').val(),
                title: $('#resort-review-form [name="title"]').val(),
                content: $('#resort-review-form [name="content"]').val()
            };

            $.post(resortData.ajax_url, data, (res) => {
                if (res.success) {
                    alert('Thank you for your review!');
                    $('#resort-review-modal').hide();
                    window.location.reload();
                }
            });
        }
    };

    $(document).ready(function() {
        BookingApp.init();
    });

})(jQuery);
