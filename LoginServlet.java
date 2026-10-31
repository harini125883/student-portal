package com.kpr.portal;

import java.io.IOException;
import java.sql.*;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

/** Verifies credentials with JDBC and starts a session. */
@WebServlet("/login")
public class LoginServlet extends HttpServlet {
    @Override
    protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws IOException {
        String user = req.getParameter("username");
        String pass = req.getParameter("password");
        if (user == null || pass == null || user.isBlank() || pass.isEmpty()) {
            resp.sendRedirect(req.getContextPath() + "/login.jsp?msg=Enter+username+and+password");
            return;
        }
        // PreparedStatement prevents SQL injection; password compared as SHA-256
        String sql = "SELECT username FROM users WHERE username = ? AND password_hash = SHA2(?, 256)";
        try (Connection c = DBUtil.getConnection(); PreparedStatement ps = c.prepareStatement(sql)) {
            ps.setString(1, user.trim());
            ps.setString(2, pass);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) {
                    HttpSession old = req.getSession(false);
                    if (old != null) old.invalidate();             // new session id after login (anti session-fixation)
                    HttpSession session = req.getSession(true);
                    session.setAttribute("user", rs.getString("username"));
                    session.setMaxInactiveInterval(15 * 60);       // 15 minutes
                    resp.sendRedirect(req.getContextPath() + "/students");
                    return;
                }
            }
        } catch (SQLException e) {
            log("Login failed", e);
            resp.sendRedirect(req.getContextPath() + "/login.jsp?msg=Server+error.+Try+again");
            return;
        }
        resp.sendRedirect(req.getContextPath() + "/login.jsp?msg=Invalid+username+or+password");
    }
}
