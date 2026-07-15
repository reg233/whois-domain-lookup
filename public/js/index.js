const form = document.getElementById("form");
const oldFormData = Object.fromEntries(new FormData(form).entries());

window.addEventListener("DOMContentLoaded", () => {
  const domainElement = document.getElementById("domain");
  const domainClearElement = document.getElementById("domain-clear");

  if (domainElement.value) {
    domainClearElement.classList.add("visible");
  }

  domainElement.addEventListener("input", (e) => {
    if (e.target.value) {
      domainClearElement.classList.add("visible");
    } else {
      domainClearElement.classList.remove("visible");
    }
  });
  domainElement.addEventListener("paste", (e) => {
    try {
      const pasteData = e.clipboardData.getData("text");
      const hostname = new URL(pasteData).hostname;

      e.preventDefault();

      domainElement.select();
      if (document.queryCommandSupported("insertText")) {
        document.execCommand("insertText", false, hostname);
      } else {
        const end = domainElement.value.length;
        domainElement.setRangeText(hostname, 0, end, "end");
        domainElement.dispatchEvent(new Event("input", { bubbles: true }));
      }
    } catch (error) {}
  });

  domainClearElement.addEventListener("click", () => {
    domainElement.focus();
    domainElement.select();
    if (document.queryCommandSupported("delete")) {
      document.execCommand("delete", false);
    } else {
      domainElement.setRangeText("");
      domainElement.dispatchEvent(new Event("input", { bubbles: true }));
    }
  });

  const toggleWHOIS = document.getElementById("toggle-whois");
  const toggleRDAP = document.getElementById("toggle-rdap");
  const inputWHOIS = document.getElementById("input-whois");
  const inputRDAP = document.getElementById("input-rdap");

  const toggles = [toggleWHOIS, toggleRDAP];
  const inputs = [inputWHOIS, inputRDAP];

  toggles.forEach((toggle, index) => {
    toggle.addEventListener("click", () => {
      const active = toggle.getAttribute("aria-active") === "true";
      const nextActive = `${!active}`;

      toggle.setAttribute("aria-active", nextActive);
      inputs[index].value = nextActive === "true" ? "1" : "0";
    });
  });

  if (oldFormData.domain) {
    toggles.forEach((toggle) => {
      const active = toggle.getAttribute("aria-active") === "true";
      localStorage.setItem(toggle.id, `${+active}`);
    });
  } else {
    const whoisValue = localStorage.getItem("toggle-whois") || "0";
    const rdapValue = localStorage.getItem("toggle-rdap") || "0";

    toggles.forEach((toggle, index) => {
      if (!+whoisValue && !+rdapValue) {
        toggle.setAttribute("aria-active", "true");
        inputs[index].value = "1";
      } else {
        const active = `${!!+localStorage.getItem(toggle.id)}`;

        toggle.setAttribute("aria-active", active);
        inputs[index].value = active === "true" ? "1" : "0";
      }
    });
  }

  const searchButton = document.getElementById("search-button");

  form.addEventListener("submit", () => {
    searchButton.disabled = true;
    searchButton.dataset.loading = "true";
  });

  window.addEventListener("pageshow", (e) => {
    if (e.persisted) {
      const { domain, whois, rdap } = oldFormData;

      if (domainElement.value !== domain) {
        domainElement.focus();
        domainElement.select();

        if (domain) {
          if (document.queryCommandSupported("insertText")) {
            document.execCommand("insertText", false, domain);
          } else {
            const end = domainElement.value.length;
            domainElement.setRangeText(domain, 0, end, "end");
            domainElement.dispatchEvent(new Event("input", { bubbles: true }));
          }
        } else {
          if (document.queryCommandSupported("delete")) {
            document.execCommand("delete", false);
          } else {
            domainElement.setRangeText("");
            domainElement.dispatchEvent(new Event("input", { bubbles: true }));
          }
        }

        domainElement.blur();
      }

      if (inputWHOIS.value !== whois) {
        toggleWHOIS.setAttribute("aria-active", `${whois === "1"}`);
        inputWHOIS.value = whois;
      }

      if (inputRDAP.value !== rdap) {
        toggleRDAP.setAttribute("aria-active", `${rdap === "1"}`);
        inputRDAP.value = rdap;
      }

      if (searchButton.disabled === true) {
        searchButton.disabled = false;
        searchButton.dataset.loading = "false";
      }
    }
  });

  const backToTop = document.getElementById("back-to-top");
  backToTop.addEventListener("click", () => {
    const body = document.body;
    const bodyStyle = window.getComputedStyle(body);
    const scrollElement = bodyStyle.overflow === "auto" ? body : window;

    scrollElement.scrollTo({ behavior: "smooth", top: 0 });
  });

  const messageElement = document.getElementById("message");
  if (messageElement) {
    const observer = new IntersectionObserver(
      ([e]) => {
        if (e.isIntersecting || e.boundingClientRect.top > 0) {
          backToTop.classList.remove("visible");
        } else {
          backToTop.classList.add("visible");
        }
      },
      { threshold: 1 },
    );
    observer.observe(messageElement);
  }
});
