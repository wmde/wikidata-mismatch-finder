interface Dimensions {
    width: number,
    height: number
};


/**
 * Inserts a test element into the document to sample the width and height of
 * scrollbars in the particular browser.
 */
export function getScrollbarDimensions(): Dimensions {
    const { body } = document;
    const scrollDiv : HTMLElement = document.createElement('div');

    // Append element with defined styling
    scrollDiv.setAttribute(
        'style',
        'width: 666px; height: 666px; position: absolute; left: -9999px; overflow: scroll;'
    );
    body.appendChild(scrollDiv);

    // Collect width & height of scrollbar
    const calculateValue = (type: 'Width' | 'Height') => scrollDiv[`offset${type}`] - scrollDiv[`client${type}`];
    const scrollbarWidth = calculateValue('Width');
    const scrollbarHeight = calculateValue('Height');

    // Remove element
    body.removeChild(scrollDiv);

    return {
        width: scrollbarWidth,
        height: scrollbarHeight
    };
}
