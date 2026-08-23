import React from "react";

export const Alert = ({ type, message }) => {
  if (!message) return null;

  const isError = type === "error";
  return (
    <div className={`alert alert-${isError ? "error" : "success"}`}>
      <strong>{isError ? "Error:" : "Import Complete!"}</strong> {message}
    </div>
  );
};
