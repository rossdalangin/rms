(function($) {
    'use strict';

    const BookingApp = {
        state: {
            step: 1,
            checkin: '',
            checkout: '',
            guests: 1,
            selectedRoom: null,
            guestData: {}
        },

        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('click', '#resort-search-btn', this.handleSearch.bind(this));
            $(document).on('click', '.select-room-btn', this.handleRoomSelection.bind(this));
            $(document).on('submit', '#resort-guest-form', this.handleGuestForm.bind(this));
            $(document).on('click', '#resort-complete-booking', this.handleCompleteBooking.bind(this));
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
                $(clone).find('.room-price').text(`$${room.price} / night`);
                $(clone).find('img').attr('src', room.image || '');
                $(clone).find('.select-room-btn').data('room', room);
                $grid.append(clone);
            });
        },

        handleRoomSelection: function(e) {
            this.state.selectedRoom = $(e.currentTarget).data('room');
            this.goToStep(3);
        },

        handleGuestForm: function(e) {
            e.preventDefault();
            const formData = $(e.currentTarget).serializeArray();
            formData.forEach(item => {
                this.state.guestData[item.name] = item.value;
            });
            this.goToStep(4);
            this.renderSummary();
        },

        renderSummary: function() {
            const $summary = $('.booking-summary');
            $summary.html(`
                <p><strong>Room:</strong> ${this.state.selectedRoom.title}</p>
                <p><strong>Dates:</strong> ${this.state.checkin} to ${this.state.checkout}</p>
                <p><strong>Guest:</strong> ${this.state.guestData.first_name} ${this.state.guestData.last_name}</p>
            `);
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
                    guest_data: this.state.guestData
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
                        this.showConfirmation(bookingId);
                    } else {
                        alert('Payment failed.');
                    }
                }
            });
        },

        showConfirmation: function(bookingId) {
            $('#resort-payment-screen').hide();
            $('#resort-confirmation-screen').show();
            $('#resort-conf-id').text(bookingId);
        }
    };

    $(document).ready(function() {
        BookingApp.init();
    });

})(jQuery);
