import React from "react";

export const ValidationTable = ({ records }) => {
  if (!records || records.length === 0) return null;

  return (
    <div className="table-container">
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Surname</th>
            <th>Email</th>
            <th>Status</th>
            <th>Details</th>
          </tr>
        </thead>
        <tbody>
          {records.map((rec, idx) => (
            <tr key={idx}>
              <td>
                {rec.name || <em style={{ color: "#94a3b8" }}>(Empty)</em>}
              </td>
              <td>
                {rec.surname || <em style={{ color: "#94a3b8" }}>(Empty)</em>}
              </td>
              <td>
                {rec.email || <em style={{ color: "#94a3b8" }}>(Empty)</em>}
              </td>
              <td>
                <span
                  className={`badge ${rec.is_valid ? "badge-valid" : "badge-error"}`}
                >
                  {rec.status}
                </span>
              </td>
              <td
                style={{
                  color: rec.is_valid ? "#64748b" : "#dc2626",
                  fontSize: "13px",
                }}
              >
                {rec.is_valid ? "Ready to import" : rec.error_message}
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
};
