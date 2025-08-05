# Fluent Form Distance Calculator

This snippet uses **Google's Distance Matrix API** to calculate the distance between two locations based on input fields in a **Fluent Forms** form.

---

## Requirements

To use this snippet, you’ll need:

- A **Google Cloud** account with the **Distance Matrix API** enabled  
- Your **Google API key**  
- The **Fluent Forms** plugin installed and activated

---

## Installation & Setup

1. Add the snippet to your `functions.php` file **or** use a code snippet plugin.
2. Replace the placeholder with your **Google API key**  
   > **Tip:** Restrict your API key in the [Google Cloud Console](https://console.cloud.google.com/) to avoid misuse.
3. Set your **base postcode** in the snippet.
4. Update the **field ID** where the calculated distance should be displayed.
5. Ensure the **source field name attribute** matches the input used for the destination postcode.
6. Now you can use the field in you calculations

---

## 📌 Notes

- This script currently works only on **single-page forms** (not multi-step).
- Be sure to test thoroughly after implementation to ensure proper functionality with your form setup.

---

## 📚 Related Article

Read the full tutorial on how this snippet works and how to customize it in the blog post:

👉 [How to Calculate Distance Using Fluent Forms and Google Maps API](https://moritzreitz.com/blog/web-development/postcode-distance-calculator-fluentform/)  

---

## 👤 Author

**Moritz Reitz**  
[Website ](https://moritzreitz.com) | [GitHub](https://github.com/Moreit11) | [LinkedIn](https://www.linkedin.com/in/moritz-reitz-866220154/)

---

## 📄 License

This project is open-source. Feel free to modify or improve it for your own use.
