window.addEventListener("DOMContentLoaded", () => {
  const updateDateElementText = (elementId) => {
    const element = document.getElementById(elementId);
    if (element) {
      const iso8601 = element.dataset.iso8601;
      if (iso8601) {
        if (iso8601.endsWith("Z")) {
          const date = new Date(iso8601);

          const year = date.getFullYear();
          const month = `${date.getMonth() + 1}`.padStart(2, "0");
          const day = `${date.getDate()}`.padStart(2, "0");
          const hours = `${date.getHours()}`.padStart(2, "0");
          const minutes = `${date.getMinutes()}`.padStart(2, "0");
          const seconds = `${date.getSeconds()}`.padStart(2, "0");

          element.innerText = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

          const timezoneOffset = date.getTimezoneOffset();

          const offsetHours = -Math.trunc(timezoneOffset / 60);
          const sign = offsetHours >= 0 ? "+" : "-";
          const offsetMinutes = Math.abs(timezoneOffset % 60);
          const minutesStr = offsetMinutes ? `:${offsetMinutes}` : "";

          const timezoneElement = document.createElement("span");
          timezoneElement.className = "card-item-value-secondary";
          timezoneElement.innerText = `UTC${sign}${Math.abs(offsetHours)}${minutesStr}`;

          element.parentElement.appendChild(timezoneElement);
        } else {
          element.innerText = iso8601;
        }
      }
    }
  };

  updateDateElementText("creation-date");
  updateDateElementText("expiration-date");
  updateDateElementText("updated-date");
  updateDateElementText("available-date");

  const updateDaysElementText = (elementId) => {
    const element = document.getElementById(elementId);
    if (element) {
      const seconds = element.dataset.seconds;
      if (seconds) {
        let days = Math.trunc(seconds / 24 / 60 / 60);
        if (seconds < 0 && days === 0) {
          days = "-0";
        }

        element.innerText = `${element.innerText} (${days} days)`;
      }
    }
  };

  updateDaysElementText("createdAgo");
  updateDaysElementText("expiresIn");
  updateDaysElementText("updatedAgo");
  updateDaysElementText("availableIn");

  const setupDNSRecords = () => {
    const view = document.getElementById("dns-records-view");
    const dialog = document.getElementById("dns-records-dialog");
    const dialogClose = dialog.querySelector(".dialog-close");
    const form = dialog.querySelector("form");
    const inputBox = form.querySelector(".subdomain-input-box");
    const input = form.querySelector("input");
    const queryButton = form.querySelector("button");
    const multiStatus = dialog.querySelector(".multi-status");
    const result = dialog.querySelector(".dns-records-result");

    if (!view) {
      return;
    }

    if (typeof HTMLDialogElement !== "function") {
      const cardItem = view.closest(".card-item");
      if (cardItem.parentElement.childElementCount === 1) {
        cardItem.closest(".card").remove();
      } else {
        cardItem.remove();
      }
      return;
    }

    view.addEventListener("click", () => {
      dialog.showModal();
      getData();
    });
    dialog.addEventListener("click", (e) => {
      if (e.target === dialog) {
        dialog.close();
      }
    });
    dialog.addEventListener("close", () => {
      input.value = "";
      queryButton.disabled = false;
      queryButton.dataset.loading = "false";

      controller.abort();
      if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = undefined;
      }
    });
    dialogClose.addEventListener("click", () => {
      dialog.close();
    });
    form.addEventListener("submit", (e) => {
      e.preventDefault();

      getData(new FormData(form).get("subdomain"));
    });
    inputBox.addEventListener("click", () => {
      input.focus();
    });

    let controller = new AbortController();
    let timeoutId;

    const getData = async (subdomain) => {
      queryButton.disabled = true;
      queryButton.dataset.loading = true;
      multiStatus.dataset.statusType = "loading";

      if (controller.abort) {
        controller = new AbortController();
      }
      if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = undefined;
      }

      const startTime = Date.now();

      try {
        const params = new URLSearchParams();
        params.append("domain", oldFormData.domain);
        if (subdomain) {
          params.append("subdomain", subdomain);
        }

        const response = await fetch(`dns-records?${params.toString()}`, {
          signal: controller.signal,
        });

        if (!response.ok) {
          throw new Error();
        }

        const { domain, data } = await response.json();

        let innerHTML = "";

        for (const type in data) {
          const records = data[type];

          innerHTML += `<p class="dns-records-result-type">${type}</p>`;

          innerHTML += `<table class="dns-records-result-table">`;

          innerHTML += "<thead><tr><th>#</th>";
          const keys = Object.keys(records[0]);
          keys.forEach((key) => {
            innerHTML += `<th>${key}</th>`;
          });
          innerHTML += "</tr></thead>";

          innerHTML += "<tbody>";
          records.forEach((record, index) => {
            innerHTML += `<tr><td>${index + 1}</td>`;
            keys.forEach((key) => {
              let child = record[key];
              if ((type === "A" || type === "AAAA") && key === "value") {
                child = `<a href="https://ipinfo.io/${child}" rel="nofollow noopener noreferrer" target="_blank">${child}</a>`;
              }
              innerHTML += `<td>${child}</td>`;
            });
            innerHTML += `</tr>`;
          });
          innerHTML += "</tbody>";

          innerHTML += "</table>";
        }

        if (innerHTML) {
          innerHTML = `<span class="dns-records-result-title">DNS records for ${domain}</span>${innerHTML}`;
        }

        timeoutId = setTimeout(
          () => {
            queryButton.disabled = false;
            queryButton.dataset.loading = "false";
            multiStatus.dataset.statusType = innerHTML ? "" : "empty";
            result.innerHTML = innerHTML;
          },
          Math.max(0, 500 - (Date.now() - startTime)),
        );
      } catch (error) {
        if (error.name !== "AbortError") {
          timeoutId = setTimeout(
            () => {
              queryButton.disabled = false;
              queryButton.dataset.loading = "false";
              multiStatus.dataset.statusType = "error";
            },
            Math.max(0, 500 - (Date.now() - startTime)),
          );
        }
      }
    };
  };

  setupDNSRecords();

  const cardRawData = document.getElementById("card-raw-data");
  const rawDataSentinel = document.getElementById("raw-data-sentinel");
  const rawDataHead = document.getElementById("raw-data-head");
  const rawDataTabWHOIS = document.getElementById("raw-data-tab-whois");
  const rawDataTabRDAP = document.getElementById("raw-data-tab-rdap");
  const rawDataButtons = document.getElementById("raw-data-buttons");
  const expandAllButton = document.getElementById("expand-all-button");
  const collapseAllButton = document.getElementById("collapse-all-button");
  const copyButton = document.getElementById("copy-button");
  const rawDataWHOIS = document.getElementById("raw-data-whois");
  const rawDataRDAP = document.getElementById("raw-data-rdap");

  if (rawDataSentinel && rawDataHead) {
    const observer = new IntersectionObserver(
      ([e]) => cardRawData.classList.toggle("is-sticky", !e.isIntersecting),
      { threshold: 1 },
    );
    observer.observe(rawDataSentinel);
  }

  if (rawDataTabWHOIS && rawDataTabRDAP) {
    rawDataTabWHOIS.addEventListener("click", () => {
      if (!rawDataTabWHOIS.classList.contains("raw-data-tab-active")) {
        rawDataButtons.classList.add("raw-data-buttons-only-copy");
        rawDataTabWHOIS.classList.add("raw-data-tab-active");
        rawDataWHOIS.style.display = "block";
        rawDataTabRDAP.classList.remove("raw-data-tab-active");
        rawDataRDAP.style.display = "none";
      }

      rawDataSentinel.scrollIntoView({ behavior: "smooth" });
    });
    rawDataTabRDAP.addEventListener("click", () => {
      if (!rawDataTabRDAP.classList.contains("raw-data-tab-active")) {
        rawDataButtons.classList.remove("raw-data-buttons-only-copy");
        rawDataTabWHOIS.classList.remove("raw-data-tab-active");
        rawDataWHOIS.style.display = "none";
        rawDataTabRDAP.classList.add("raw-data-tab-active");
        rawDataRDAP.style.display = "block";
      }

      rawDataSentinel.scrollIntoView({ behavior: "smooth" });
    });
  }

  const copyToClipboard = (data) => {
    if (navigator.clipboard) {
      navigator.clipboard.writeText(data);
    } else {
      const fakeElement = document.createElement("textarea");
      fakeElement.style.border = "0";
      fakeElement.style.fontSize = "12pt";
      fakeElement.style.margin = "0";
      fakeElement.style.padding = "0";
      fakeElement.style.position = "absolute";

      const isRTL = document.documentElement.getAttribute("dir") === "rtl";
      fakeElement.style[isRTL ? "right" : "left"] = "-9999px";
      const yPosition =
        window.pageYOffset || document.documentElement.scrollTop;
      fakeElement.style.top = `${yPosition}px`;

      fakeElement.setAttribute("readonly", "");
      fakeElement.value = data;

      document.body.appendChild(fakeElement);

      fakeElement.select();
      fakeElement.setSelectionRange(0, fakeElement.value.length);

      document.execCommand("copy");

      fakeElement.remove();
    }
  };

  if (copyButton) {
    let timeoutId;

    copyButton.addEventListener("click", () => {
      let data;

      if (rawDataWHOIS && getComputedStyle(rawDataWHOIS).display === "block") {
        data = rawDataWHOIS.innerText;
      } else if (
        rawDataRDAP &&
        getComputedStyle(rawDataRDAP).display === "block"
      ) {
        data = rdapData;
      }

      if (!data) {
        return;
      }

      if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
      }

      copyToClipboard(data);
      copyButton.dataset.copied = "true";
      timeoutId = setTimeout(() => (copyButton.dataset.copied = "false"), 2333);
    });
  }

  const linkifyRawData = (element) => {
    if (element) {
      element.innerHTML = linkifyHtml(element.innerHTML, {
        rel: "nofollow noopener noreferrer",
        target: "_blank",
        validate: {
          url: (value) => /^https?:\/\//i.test(value),
        },
      });
    }
  };

  if (rawDataWHOIS) {
    linkifyRawData(rawDataWHOIS);
  }

  let rdapData = "";
  if (rawDataRDAP) {
    rdapData = rawDataRDAP.textContent;

    try {
      const data = JSON.parse(rdapData);
      setupJSONViewer(rawDataRDAP, expandAllButton, collapseAllButton, data);
    } catch (error) {}

    linkifyRawData(rawDataRDAP);
  }

  tippy.createSingleton(tippy("#raw-data-buttons button"), {
    appendTo: "parent",
    arrow: false,
    hideOnClick: false,
    offset: [0, 8],
    placement: "bottom",
    moveTransition: "transform 233ms ease",
  });
});
