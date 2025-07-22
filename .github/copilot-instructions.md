<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

# Figger Energy SAS Corporate Website - Development Guidelines

## Project Context
This is a corporate website for Figger Energy SAS, a Colombian company specializing in:
- Sustainable mining operations management
- Personnel training and capacity building
- Ecosystem rehabilitation
- Management of large volumes of sensitive worker data

## Technical Stack
- Frontend: HTML5, CSS3, JavaScript
- Backend: PHP
- Database: MySQL
- Server: Apache2 via XAMPP
- Target OS: Windows 10 (XAMPP environment)

## Security Requirements
- Implement secure authentication with password hashing
- Protect against SQL injection
- Implement session management with timeout
- Include access logging for auditing
- Different access levels: Employees, Administrators, Auditors

## Design Guidelines
- Corporate colors: Green (sustainable energy), Blue (trust), Gray (professionalism)
- Professional, clean, responsive design
- Modern typography (Roboto, Open Sans)
- Energy, mining, and sustainability iconography

## Code Quality Standards
- Clean, commented code
- Separation of concerns (HTML, CSS, JS, PHP)
- Modular and scalable structure
- Include basic documentation

## User Types and Access Levels
1. Employees (Técnicos y Operarios): Basic access
2. Administrators (Jefes, Coordinadores): Intermediate access  
3. Auditors (CISO, CEO, CTO): Complete access
