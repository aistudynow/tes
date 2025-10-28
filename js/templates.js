// templates.js
const API_BASE = "./php";

function updateDBStatus(status, message) {
  const statusEl = document.getElementById("dbStatus");
  const textEl = document.getElementById("dbStatusText");
  if (statusEl && textEl) {
    statusEl.className = `db-status ${status}`;
    textEl.textContent = message;
  }
}

async function loadTemplates() {
  updateDBStatus('checking', 'Connecting...');
  try {
    const res = await fetch(`${API_BASE}/get_templates.php`);
    const ct = res.headers.get("content-type") || "";
    const raw = await res.text();

    if (!ct.includes("application/json")) {
      console.error("Non-JSON response from server:\n", raw);
      updateDBStatus('error', 'Server error');
      alert("Server error. Check console for details or visit php/test_connection.php to diagnose.");
      return;
    }

    const data = JSON.parse(raw);

    // Check if there's an error in the response
    if (data.error) {
      console.error("Database error:", data);
      updateDBStatus('error', 'DB connection failed');
      let errorMsg = `Database Error: ${data.error}`;
      if (data.hint) {
        errorMsg += `\n\n${data.hint}`;
      }
      if (data.attempts) {
        errorMsg += `\n\nConnection attempts: ${data.attempts.join(', ')}`;
      }
      alert(errorMsg + "\n\nVisit php/test_connection.php in your browser for detailed diagnostics.");
      return;
    }

    const select = document.getElementById("templateSelect");
    if (!select) return;

    select.innerHTML = `<option value="">Select Template...</option>`;
    if (Array.isArray(data)) {
      data.forEach((t) => {
        const opt = document.createElement("option");
        opt.value = t.id;
        opt.textContent = t.title;
        select.appendChild(opt);
      });
      updateDBStatus('connected', `Connected (${data.length} templates)`);
    } else {
      updateDBStatus('connected', 'Connected');
    }
  } catch (err) {
    console.error("loadTemplates error:", err);
    updateDBStatus('error', 'Connection error');
    alert(`Could not load templates: ${err.message}\n\nVisit php/test_connection.php in your browser to test the database connection.`);
  }
}

async function fetchTemplate(id) {
  try {
    if (!id) return;
    const res = await fetch(`${API_BASE}/get_template.php?id=${encodeURIComponent(id)}`);
    const ct = res.headers.get("content-type") || "";
    const raw = await res.text();

    if (!ct.includes("application/json")) {
      console.error("Non-JSON from server:\n", raw.slice(0, 600));
      alert("Server did not return JSON. See console.");
      return;
    }

    const data = JSON.parse(raw);
    const textarea = document.getElementById("customPromptInput");
    if (textarea) textarea.value = data.content || "";
  } catch (err) {
    console.error("fetchTemplate error:", err);
    alert("Could not fetch template.");
  }
}

async function saveTemplate() {
  try {
    const title = prompt("Enter a title for this template:");
    if (!title) return;

    const content = document.getElementById("customPromptInput").value.trim();
    if (!content) return alert("Custom prompt is empty.");

    const formData = new FormData();
    formData.append("title", title);
    formData.append("content", content);

    const res = await fetch(`${API_BASE}/save_template.php`, { method: "POST", body: formData });
    const ct = res.headers.get("content-type") || "";
    const raw = await res.text();

    if (!ct.includes("application/json")) {
      console.error("Non-JSON from save_template:\n", raw.slice(0, 600));
      alert("Server did not return JSON on save. See console.");
      return;
    }

    const data = JSON.parse(raw);
    if (data.success) {
      alert("Template saved.");
      loadTemplates();
    } else {
      alert("Save failed.");
    }
  } catch (err) {
    console.error("saveTemplate error:", err);
    alert("Could not save template.");
  }
}

async function updateTemplate() {
  try {
    const id = document.getElementById("templateSelect").value;
    if (!id) return alert("Select a template first.");

    const content = document.getElementById("customPromptInput").value.trim();
    if (!content) return alert("Custom prompt is empty.");

    const formData = new FormData();
    formData.append("id", id);
    formData.append("content", content);

    const res = await fetch(`${API_BASE}/update_template.php`, { method: "POST", body: formData });
    const ct = res.headers.get("content-type") || "";
    const raw = await res.text();

    if (!ct.includes("application/json")) {
      console.error("Non-JSON from update_template:\n", raw.slice(0, 600));
      alert("Server did not return JSON on update. See console.");
      return;
    }

    const data = JSON.parse(raw);
    alert(data.success ? "Template updated." : "Update failed.");
  } catch (err) {
    console.error("updateTemplate error:", err);
    alert("Could not update template.");
  }
}

async function deleteTemplate() {
  try {
    const id = document.getElementById("templateSelect").value;
    if (!id) return alert("Select a template first.");
    if (!confirm("Delete this template?")) return;

    const formData = new FormData();
    formData.append("id", id);

    const res = await fetch(`${API_BASE}/delete_template.php`, { method: "POST", body: formData });
    const ct = res.headers.get("content-type") || "";
    const raw = await res.text();

    if (!ct.includes("application/json")) {
      console.error("Non-JSON from delete_template:\n", raw.slice(0, 600));
      alert("Server did not return JSON on delete. See console.");
      return;
    }

    const data = JSON.parse(raw);
    if (data.success) {
      alert("Template deleted.");
      document.getElementById("customPromptInput").value = "";
      loadTemplates();
    } else {
      alert("Delete failed.");
    }
  } catch (err) {
    console.error("deleteTemplate error:", err);
    alert("Could not delete template.");
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadTemplates();
  const sel = document.getElementById("templateSelect");
  if (sel) sel.addEventListener("change", (e) => fetchTemplate(e.target.value));
  document.getElementById("saveTemplateBtn")?.addEventListener("click", saveTemplate);
  document.getElementById("updateTemplateBtn")?.addEventListener("click", updateTemplate);
  document.getElementById("deleteTemplateBtn")?.addEventListener("click", deleteTemplate);
});

// expose for inline
window.loadTemplates = loadTemplates;
window.fetchTemplate = fetchTemplate;
window.saveTemplate = saveTemplate;
window.updateTemplate = updateTemplate;
window.deleteTemplate = deleteTemplate;
