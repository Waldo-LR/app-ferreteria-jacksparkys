<script>
    const purchaseLines = document.getElementById('purchase-lines');
    const purchaseLineTemplate = document.getElementById('purchase-line-template');
    let purchaseLineIndex = purchaseLines.children.length;

    document.getElementById('add-purchase-line')?.addEventListener('click', () => {
        const line = purchaseLineTemplate.innerHTML.replaceAll('__INDEX__', String(purchaseLineIndex++));
        purchaseLines.insertAdjacentHTML('beforeend', line);
    });

    purchaseLines?.addEventListener('click', (event) => {
        if (event.target.closest('.remove-purchase-line')) {
            event.target.closest('.purchase-line').remove();
        }
    });

    purchaseLines?.addEventListener('change', (event) => {
        if (event.target.matches('.purchase-product')) {
            const selectedOption = event.target.selectedOptions[0];
            const costInput = event.target.closest('.purchase-line').querySelector('.purchase-cost');

            if (selectedOption?.dataset.costo) {
                costInput.value = selectedOption.dataset.costo;
            }
        }
    });
</script>
