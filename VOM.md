# Validation Ownership Matrix

Essentially, this is my own methodology to distinguish and separate validation places, since in big applications, validation can happen in both frontend and backend, and tracking and managing code can be difficult.

This matrix helps us developer understand in what part validation should live inside the code, this way we never have to be confused again why there is some obscure check in the react arrow function frontend.

<table style="width: 100%; border-collapse: collapse; border: 1px solid #30363d;">
  <thead>
    <tr style="border-bottom: 1px solid #30363d;">
      <td align="left" style="padding: 10px; font-weight: bold; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">Rule</td>
      <td align="center" style="padding: 10px; font-weight: bold; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">React Comp</td>
      <td align="center" style="padding: 10px; font-weight: bold; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">React API</td>
      <td align="center" style="padding: 10px; font-weight: bold; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">Backend</td>
      <td align="left" style="padding: 10px; font-weight: bold; border-bottom: 1px solid #30363d;">Requirement</td>
    </tr>
  </thead>
  <tbody>
    <tr style="border-bottom: 1px solid #30363d;">
      <td style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;"><b>CSV Extension</b></td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td style="padding: 10px; border-bottom: 1px solid #30363d;">Must be CSV</td>
    </tr>
    <tr style="border-bottom: 1px solid #30363d;">
      <td style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;"><b>File Size</b></td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td style="padding: 10px; border-bottom: 1px solid #30363d;">Max 50 MB</td>
    </tr>
    <tr style="border-bottom: 1px solid #30363d;">
      <td style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;"><b>CSV Parsing</b></td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td style="padding: 10px; border-bottom: 1px solid #30363d;">Valid CSV</td>
    </tr>
    <tr style="border-bottom: 1px solid #30363d;">
      <td style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;"><b>Required Columns</b></td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td style="padding: 10px; border-bottom: 1px solid #30363d;">Name, Surname, Email</td>
    </tr>
    <tr style="border-bottom: 1px solid #30363d;">
      <td style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;"><b>Email Format</b></td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td style="padding: 10px; border-bottom: 1px solid #30363d;">Valid email</td>
    </tr>
    <tr style="border-bottom: 1px solid #30363d;">
      <td style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;"><b>Email Uniqueness</b></td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">-</td>
      <td align="center" style="padding: 10px; border-right: 1px solid #30363d; border-bottom: 1px solid #30363d;">✓</td>
      <td style="padding: 10px; border-bottom: 1px solid #30363d;">Must be unique</td>
    </tr>
  </tbody>
</table>

<br>
This example uses very simple responsibilities, you can arguably add more stuff the bigger the application gets and modify the matrix to separate by API calls or features.
