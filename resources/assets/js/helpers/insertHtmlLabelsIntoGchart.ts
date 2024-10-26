/**
 * Replaces gchart text labels with given html labels array
 * @param {*} gChartEl - reference to google chart to update
 * @param {*} htmlLabelsArray - array of html labels like `<p>Label 1</p>`
 */
export default function insertHtmlLabelsIntoGchart(gChartEl, htmlLabelsArray) {
  // get labels that contain html paragraphs
  const currentTextLabels = [...gChartEl.querySelectorAll("text")].filter(
    (el) => /<p>/.test(el.textContent)
  );

  const currentForeignObjectLabels = gChartEl.querySelectorAll("foreignObject");

  const allLabels = [...currentTextLabels, ...currentForeignObjectLabels];

  // we'll use the chart bars to help with positioning
  // getting bars by stroke color... a bit hacky, but it works
  const bars = [...gChartEl.querySelectorAll('[stroke="#36a2eb"]')];

  // replace each text node with a `<foreignObject>` element
  // containing the HTML
  allLabels.forEach((label, index) => {
    const bar = bars[index];

    // if no bar, don't bother with the label
    if (!bar) {
      label.parentNode.innerHTML = "";
      return;
    }

    // use bar attributes for placing label
    const x = bar.getAttribute("x");
    const width = bar.getAttribute("width");
    const y =
      Number.parseFloat(bar.getAttribute("y")) +
      Number.parseFloat(bar.getAttribute("height")) +
      16;

    // set some height with overflow visible to make sure that
    // foreignObject isn't clipped
    label.parentNode.innerHTML = `
                <foreignObject width="${width}" height="1" x=${x} y=${y} style="overflow: visible;">
                  ${htmlLabelsArray[index]}
                </foreignObject>
              `;
  });
}
