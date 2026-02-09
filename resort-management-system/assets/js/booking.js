(function($) {
    'use strict';

    const BookingApp = {
        state: {
            step: 1,
            checkin: '',
            checkout: '',
            guests: 1,
            selectedRoom: null,
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
            $(document).on('click', '#resort-services-next', this.handleServicesSelection.bind(this));
            $(document).on('submit', '#resort-guest-form', this.handleGuestForm.bind(this));
            $(document).on('click', '#resort-apply-coupon', this.handleCouponApply.bind(this));
            $(document).on('click', '#resort-complete-booking', this.handleCompleteBooking.bind(this));

            // Review Modal
            $(document).on('click', '.show-review-form', (e) => {
                $('#review-booking-id').val($(e.currentTarget).data('booking'));
                $('#resort-review-modal').show();
            });
            $(document).on('click', '.close-modal', () => $('#resort-review-modal').hide());
            $(document).on('submit', '#resort-review-form', this.handleReviewSubmit.bind(this));
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

            $.ajax({
                url: `${resortData.api_url}/availability`,
                data: { checkin, checkout, guests },
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
            this.state.selectedRoom = $(e.currentTarget).data('room');
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

            services.forEach(service => {
                const clone = template.content.cloneNode(true);
                $(clone).find('.service-title').text(service.title);
                $(clone).find('.service-price').text(this.formatPrice(service.price));
                $(clone).find('.resort-service-checkbox').val(service.id).data('price', service.price).data('title', service.title);
                $list.append(clone);
            });
        },

        handleServicesSelection: function() {
            this.state.selectedServices = [];
            $('.resort-service-checkbox:checked').each((i, el) => {
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
            let servicesHtml = '';
            let servicesTotal = 0;
            if (this.state.selectedServices.length > 0) {
                servicesHtml = '<p><strong>Extras:</strong> ' + this.state.selectedServices.map(s => s.title).join(', ') + '</p>';
                this.state.selectedServices.forEach(s => servicesTotal += parseFloat(s.price));
            }
            const grandTotal = parseFloat(this.state.selectedRoom.price) + servicesTotal;

            $summary.html(`
                <p><strong>Room:</strong> ${this.state.selectedRoom.title}</p>
                <p><strong>Dates:</strong> ${this.state.checkin} to ${this.state.checkout}</p>
                ${servicesHtml}
                <p><strong>Subtotal:</strong> ${this.formatPrice(grandTotal)}</p>
                <div id="discount-display" style="color: #d63638; display:none;"></div>
                <p><strong>Total Price:</strong> <span id="grand-total-display-container"></span></p>
                <p><strong>Guest:</strong> ${this.state.guestData.first_name} ${this.state.guestData.last_name}</p>
            `);
            $('#grand-total-display-container').text(this.formatPrice(grandTotal));
            this.state.finalTotal = grandTotal;
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
                    $('#coupon-message').text('Coupon applied!').css('color', 'green');
                } else {
                    $('#coupon-message').text(res.data.message).css('color', 'red');
                }
            });
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
                    room_id: this.state.selectedRoom.id,
                    checkin: this.state.checkin,
                    checkout: this.state.checkout,
                    guest_data: this.state.guestData,
                    services: this.state.selectedServices.map(s => s.id),
                    payment_method: paymentMethod,
                    coupon: this.state.couponCode || '',
                    final_total: this.state.finalTotal
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
