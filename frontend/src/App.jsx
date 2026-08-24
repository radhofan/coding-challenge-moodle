import React, { useState } from "react";
import { Header } from "./components/Header";
import { Alert } from "./components/Alert";
import { FileUploadZone } from "./components/FileUploadZone";
import { StatsSummary } from "./components/StatsSummary";
import { ValidationTable } from "./components/ValidationTable";
import { ActionBar } from "./components/ActionBar";
import { previewCsv, importCsv } from "./api/userImport";

export default function App() {
  const [selectedFile, setSelectedFile] = useState(null);
  const [csvText, setCsvText] = useState("");
  const [previewResult, setPreviewResult] = useState(null);
  const [importResult, setImportResult] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024; // 50 MB

  const validateFile = (file) => {
    if (!file.name.toLowerCase().endsWith(".csv")) {
      throw new Error("Invalid file type. File must be a .csv file.");
    }
    if (file.size > MAX_FILE_SIZE_BYTES) {
      throw new Error("File size exceeds the maximum limit of 50 MB.");
    }
  };

  const handleFileSelect = async (file) => {
    setSelectedFile(file);
    setImportResult(null);

    try {
      validateFile(file);
      setLoading(true);
      setError(null);
      const data = await previewCsv(file, null);
      setPreviewResult(data);
      const text = await file.text();
      setCsvText(text);
    } catch (err) {
      setError(
        err instanceof Error ? err.message : "An error occurred during file parsing"
      );
      setPreviewResult(null);
    } finally {
      setLoading(false);
    }
  };

  const handleImport = async () => {
    if (!previewResult || previewResult.valid === 0) return;

    setLoading(true);
    setError(null);

    try {
      const data = await importCsv(selectedFile, csvText);
      setImportResult(data);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (err) {
      setError(
        err instanceof Error ? err.message : "An error occurred during user import"
      );
    } finally {
      setLoading(false);
    }
  };

  const handleReset = () => {
    setSelectedFile(null);
    setCsvText("");
    setPreviewResult(null);
    setImportResult(null);
    setError(null);
  };

  return (
    <div className="container">
      <Header />

      <Alert type="error" message={error} />
      {importResult && (
        <Alert
          type="success"
          message={`Successfully imported ${importResult.imported} valid user records into PostgreSQL.`}
        />
      )}

      {!previewResult ? (
        <FileUploadZone
          onFileSelect={handleFileSelect}
          onError={(msg) => setError(msg)}
        />
      ) : (
        <>
          <StatsSummary stats={previewResult} />

          <div className="card">
            <div
              style={{
                display: "flex",
                justifyContent: "space-between",
                alignItems: "center",
                marginBottom: "16px",
              }}
            >
              <h2 style={{ fontSize: "18px", fontWeight: "600" }}>
                CSV Validation Preview
              </h2>
              <button
                className="btn btn-secondary"
                onClick={handleReset}
                disabled={loading}
              >
                Upload Another File
              </button>
            </div>

            <ValidationTable records={previewResult.records} />

            <ActionBar
              validCount={previewResult.valid}
              totalCount={previewResult.total}
              loading={loading}
              importResult={importResult}
              onImport={handleImport}
            />
          </div>
        </>
      )}
    </div>
  );
}
