import { BrowserRouter, Routes, Route, Navigate } from 'react-router-dom';
import Login from './pages/admin/Login.jsx';
import Dashboard from './pages/admin/Dashboard.jsx';
import SurveyBuilder from './pages/admin/SurveyBuilder.jsx';
import SurveyResponses from './pages/admin/SurveyResponses.jsx';
import SurveyTake from './pages/public/SurveyTake.jsx';

function RequireAuth({ children }) {
  const token = localStorage.getItem('token');
  return token ? children : <Navigate to="/admin/login" replace />;
}

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        {/* Public: this is the page respondents land on after scanning the QR code */}
        <Route path="/survey/:slug" element={<SurveyTake />} />

        {/* Admin */}
        <Route path="/admin/login" element={<Login />} />
        <Route path="/admin" element={<RequireAuth><Dashboard /></RequireAuth>} />
        <Route path="/admin/surveys/:id" element={<RequireAuth><SurveyBuilder /></RequireAuth>} />
        <Route path="/admin/surveys/:id/responses" element={<RequireAuth><SurveyResponses /></RequireAuth>} />

        <Route path="*" element={<Navigate to="/admin" replace />} />
      </Routes>
    </BrowserRouter>
  );
}
