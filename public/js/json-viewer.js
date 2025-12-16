function setupJSONViewer(element) {
  function valueToHtml(value, firstLevel) {
    const valueType = typeof value;

    if (value === undefined || value === null) {
      return decorateWithSpan("null", "token null");
    } else if (Array.isArray(value)) {
      return arrayToHtml(value, firstLevel);
    } else if (valueType === "boolean") {
      return decorateWithSpan(value, "token boolean");
    } else if (valueType === "number") {
      return decorateWithSpan(value, "token number");
    } else if (valueType === "object") {
      return objectToHtml(value, firstLevel);
    } else if (valueType === "string") {
      return decorateWithSpan(
        `"${JSON.stringify(value).slice(1, -1)}"`,
        "token string"
      );
    }

    return "";
  }

  function decorateWithSpan(value, className) {
    return `<span class="${className}">${htmlEncode(value)}</span>`;
  }

  function arrayToHtml(array, firstLevel) {
    if (array.length === 0) {
      return `${punctuation("[")}${punctuation("]")}`;
    }

    let output = "";
    if (!firstLevel) {
      output = `<button class="collapser" aria-label="collapse">${caretDown}</button>`;
    }
    output += punctuation("[");
    output += '<span class="ellipsis"></span>';
    output += '<ul class="array collapsible">';

    for (let i = 0; i < array.length; i++) {
      output += "<li>";
      output += valueToHtml(array[i], false);
      if (i < array.length - 1) {
        output += punctuation(",");
      }
      output += "</li>";
    }

    output += `</ul>${punctuation("]")}`;

    return output;
  }

  function objectToHtml(object, firstLevel) {
    const keys = Object.keys(object);

    if (keys.length === 0) {
      return `${punctuation("{")}${punctuation("}")}`;
    }

    let output = "";
    if (!firstLevel) {
      output = `<button class="collapser" aria-label="collapse">${caretDown}</button>`;
    }
    output += punctuation("{");
    output += '<span class="ellipsis"></span>';
    output += '<ul class="object collapsible">';

    for (let i = 0; i < keys.length; i++) {
      const key = keys[i];

      output += "<li>";
      output += `<span class="token property">"${htmlEncode(key)}"</span>`;
      output += `<span class="token operator">:</span> `;
      output += valueToHtml(object[key], false);
      if (i < keys.length - 1) {
        output += punctuation(",");
      }
      output += "</li>";
    }

    output += `</ul>${punctuation("}")}`;

    return output;
  }

  function punctuation(value) {
    return `<span class="token punctuation">${value}</span>`;
  }

  function htmlEncode(value) {
    return value === undefined
      ? ""
      : value
          .toString()
          .replace(/&/g, "&amp;")
          .replace(/"/g, "&quot;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;");
  }

  let caretDown =
    '<svg width="1em" height="1em" viewBox="0 0 640 640"><path d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z" fill="currentColor"/></svg>';
  let caretRight =
    '<svg width="1em" height="1em" viewBox="0 0 640 640"><path d="M441.3 299.8C451.5 312.4 450.8 330.9 439.1 342.6L311.1 470.6C301.9 479.8 288.2 482.5 276.2 477.5C264.2 472.5 256.5 460.9 256.5 448L256.5 192C256.5 179.1 264.3 167.4 276.3 162.4C288.3 157.4 302 160.2 311.2 169.3L439.2 297.3L441.4 299.7z" fill="currentColor"/></svg>';

  const json = JSON.parse(element.textContent);

  element.classList.add("json-viewer");
  element.innerHTML = `<code>${valueToHtml(json, true)}</code>`;

  element.addEventListener("click", (e) => {
    if (e.target.className === "collapser") {
      if (e.target.parentElement.classList.contains("collapsed")) {
        e.target.parentElement.classList.remove("collapsed");
        e.target.innerHTML = caretDown;
        e.target.setAttribute("aria-label", "collapse");
      } else {
        e.target.parentElement.classList.add("collapsed");
        e.target.innerHTML = caretRight;
        e.target.setAttribute("aria-label", "expand");
      }
    }
  });
}
