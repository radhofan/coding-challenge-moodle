import React from "react";

export const StatsSummary = ({ stats }) => {
  if (!stats) return null;

  return (
    <div className="stats-grid">
      <div className="stat-box">
        <div className="stat-label">Users Found</div>
        <div className="stat-value total">{stats.total}</div>
      </div>
      <div className="stat-box">
        <div className="stat-label">Valid Records</div>
        <div className="stat-value valid">{stats.valid}</div>
      </div>
      <div className="stat-box">
        <div className="stat-label">Invalid Records</div>
        <div className="stat-value invalid">{stats.invalid}</div>
      </div>
    </div>
  );
};
