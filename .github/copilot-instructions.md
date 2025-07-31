# Figger Energy Frontend - Copilot Instructions

<!-- Use this file to provide workspace-specific custom instructions to Copilot. For more details, visit https://code.visualstudio.com/docs/copilot/copilot-customization#_use-a-githubcopilotinstructionsmd-file -->

## Project Context
This is a frontend management system for Figger Energy SAS, a Colombian renewable energy company specializing in solar energy communities in post-mining regions.

## Development Guidelines
- Use vanilla HTML, CSS, and JavaScript (no frameworks)
- Follow Colombian energy sector UI/UX standards
- Implement role-based access control (admin, employee, auditor)
- Ensure responsive design for mobile, tablet, and desktop
- Use corporate green energy color palette
- Integrate with existing backend API at http://127.0.0.1:8000/api/
- Follow security best practices for frontend authentication
- Implement proper error handling and loading states
- Use semantic HTML and ARIA labels for accessibility
- Maintain clean, documented code with Spanish comments where appropriate

## API Integration
- Base URL: http://127.0.0.1:8000/api/
- Endpoints: /departamentos/, /empleados/, /roles/, /usuarios/, /estadisticas/, /notificaciones/
- Use proper HTTP methods (GET, POST, PUT, DELETE)
- Handle all response codes and error states
- Implement request timeouts and retry logic

## UI/UX Requirements
- Corporate colors: Primary #2c5f2d, Secondary #4a8b3a, Accent #97bf47
- Professional, governmental appearance
- Smooth animations and micro-interactions
- Loading states and error messages
- Toast notifications for user feedback
