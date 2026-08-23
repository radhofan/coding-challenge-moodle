import React from "react";

export const ActionBar = ({ validCount, totalCount, loading, importResult, onImport }) => (
  <div className="action-bar">
    <span style={{ fontSize: "14px", color: "#64748b" }}>
      {validCount} out of {totalCount} records will be imported
    </span>
    <button
      className="btn btn-primary"
      onClick={onImport}
      disabled={loading || validCount === 0 || !!importResult}
    >
      {loading
        ? "Processing..."
        : importResult
        ? "Imported"
        : `[ Import ${validCount} Users ]`}
    </button>
  </div>
);
