package com.kpr.portal;

import java.io.IOException;
import javax.servlet.*;
import javax.servlet.annotation.WebFilter;
import javax.servlet.http.*;

/** Session handling: only logged-in users may reach /students and /file. */
@WebFilter(urlPatterns = {"/students", "/file"})
public class AuthFilter implements Filter {
    @Override
    public void doFilter(ServletRequest req, ServletResponse res, FilterChain chain)
            throws IOException, ServletException {
        HttpServletRequest request = (HttpServletRequest) req;
        HttpServletResponse response = (HttpServletResponse) res;

        HttpSession session = request.getSession(false);          // do NOT create a new session
        boolean loggedIn = session != null && session.getAttribute("user") != null;

        if (loggedIn) {
            response.setHeader("Cache-Control", "no-store");      // back button won't show private pages after logout
            chain.doFilter(req, res);
        } else {
            response.sendRedirect(request.getContextPath() + "/login.jsp?msg=Please+log+in");
        }
    }
}
