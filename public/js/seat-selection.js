let selectedSeat = null;

function selectSeat(seatNumber) {
    const seatElement = document.querySelector(`.seat[data-seat="${seatNumber}"]`);
    if (seatElement.classList.contains('occupied')) {
        return; 
    }

    if (selectedSeat !== null) {
        const prevSeatElement = document.querySelector(`.seat[data-seat="${selectedSeat}"]`);
        prevSeatElement.classList.remove('selected');
    }

    seatElement.classList.add('selected');
    selectedSeat = seatNumber;

    document.getElementById('selectedSeatDisplay').textContent = seatNumber + '. Koltuk';
    document.getElementById('selectedSeatInput').value = seatNumber;

    const continueButton = document.getElementById('continueButton');
    continueButton.disabled = false;
    continueButton.classList.remove('cursor-not-allowed');
    continueButton.classList.add('cursor-pointer');
}