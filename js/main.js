// No imports. Uses global defaultEnhancements and defaultPDFs loaded before this file.

const $ = (id) => document.getElementById(id);

// Nav
const navButtons = document.querySelectorAll(".nav-btn");
const sections = document.querySelectorAll(".section");

navButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    navButtons.forEach((b) => b.classList.remove("active"));
    btn.classList.add("active");
    const target = btn.getAttribute("data-section");
    sections.forEach((sec) => {
      sec.classList.toggle("active", sec.id === target);
    });
  });
});

// Dark mode
const darkModeToggle = $("darkModeToggle");
if (localStorage.getItem("darkMode") === "true") {
  darkModeToggle.checked = true;
  document.body.classList.add("dark");
}
darkModeToggle.addEventListener("change", () => {
  document.body.classList.toggle("dark", darkModeToggle.checked);
  localStorage.setItem("darkMode", darkModeToggle.checked);
});

// Enhancements table
const enhancementBody = $("enhancementBody");
const addRowBtn = $("addRowBtn");

function addEnhancementRow(ai = "", human = "") {
  const row = document.createElement("tr");
  row.innerHTML = `
    <td><input type="text" value="${ai.replace(/"/g, "&quot;")}" placeholder="AI Phrase..."/></td>
    <td><input type="text" value="${human.replace(/"/g, "&quot;")}" placeholder="Human Phrase..."/></td>
    <td><button class="delete-row" title="Remove">x</button></td>
  `;
  enhancementBody.appendChild(row);
}
addRowBtn.addEventListener("click", () => addEnhancementRow());

enhancementBody.addEventListener("click", (e) => {
  if (e.target.classList.contains("delete-row")) e.target.closest("tr").remove();
});

// PDFs
const addPdfBtn = $("addPdfBtn");
const newPdfInput = $("newPdfInput");

addPdfBtn.addEventListener("click", () => {
  const pdfValue = newPdfInput.value.trim();
  if (!pdfValue) return;
  const pdfList = document.querySelector(".pdf-list");
  const label = document.createElement("label");
  label.innerHTML = `<input type="checkbox" class="pdf-check" value="${pdfValue}" checked/> ${pdfValue}`;
  pdfList.appendChild(label);
  newPdfInput.value = "";
});

// Generate
$("generateBtn").addEventListener("click", () => {
  const title = $("promptTitle").value.trim();
  const topic = $("topicInput").value.trim();
  const script = $("scriptInput").value.trim();
  const keywords = $("keywordsInput").value.trim();
  const template = $("customPromptInput").value.trim();

  if (!template && !topic) {
    alert("Add a template or a topic.");
    return;
  }

  // selected PDFs
  const selectedPdfs = Array.from(document.querySelectorAll(".pdf-check:checked")).map(cb => cb.value);

  // enhancement pairs
  const replacements = Array.from(enhancementBody.querySelectorAll("tr")).map((row) => {
    const inputs = row.querySelectorAll("input");
    return { aiText: inputs[0].value, humanText: inputs[1].value };
  });

  // Build from template first
  let output = template || "";

  // Replace placeholders
  output = output.replace(/\[topic\]/gi, topic);
  output = output.replace(/\[myscript\]/gi, script);

  // If user gave title, topic, keywords, add a small header above template
  let header = "";
  if (title) header += `### ${title}\n\n`;
  if (topic) header += `Topic: ${topic}\n\n`;
  if (keywords) header += `Keywords: ${keywords}\n\n`;
  if (selectedPdfs.length) header += `Use these PDF guides: ${selectedPdfs.join(", ")}\n\n`;

  output = header + output;

  // Apply humanization replacements
  replacements.forEach(({ aiText, humanText }) => {
    if (!aiText || !humanText) return;
    const regex = new RegExp(aiText.replace(/[.*+?^${}()|[\]\\]/g, "\\$&"), "gi");
    output = output.replace(regex, humanText);
  });

  $("outputText").value = output;

  // switch to output tab
  navButtons.forEach((b) => b.classList.remove("active"));
  document.querySelector(`[data-section="output"]`).classList.add("active");
  sections.forEach((sec) => sec.classList.toggle("active", sec.id === "output"));
});

// Copy / Download / Clear
$("copyBtn").addEventListener("click", () => {
  const text = $("outputText").value;
  if (!text) return alert("Nothing to copy.");
  navigator.clipboard.writeText(text);
  alert("Copied.");
});

$("downloadBtn").addEventListener("click", () => {
  const text = $("outputText").value;
  if (!text) return alert("No prompt to download.");
  const blob = new Blob([text], { type: "text/plain" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "generated_prompt.txt";
  a.click();
});

$("clearBtn").addEventListener("click", () => {
  if (!confirm("Clear all fields?")) return;
  $("outputText").value = "";
  $("promptTitle").value = "";
  $("topicInput").value = "";
  $("scriptInput").value = "";
  $("keywordsInput").value = "";
  // do not clear template so you can keep working
});

// Init defaults
window.addEventListener("DOMContentLoaded", () => {
  enhancementBody.innerHTML = "";
  (Array.isArray(defaultEnhancements) ? defaultEnhancements : []).forEach((p) => addEnhancementRow(p.aiText, p.humanText));

  const pdfList = document.querySelector(".pdf-list");
  pdfList.innerHTML = "";
  (Array.isArray(defaultPDFs) ? defaultPDFs : []).forEach((pdf) => {
    const label = document.createElement("label");
    label.innerHTML = `<input type="checkbox" class="pdf-check" value="${pdf}" checked/> ${pdf}`;
    pdfList.appendChild(label);
  });
});
