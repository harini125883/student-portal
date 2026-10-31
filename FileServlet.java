package com.kpr.portal;

import java.io.IOException;
import java.nio.file.*;
import java.sql.*;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

/** Streams an uploaded image/document: <img src="file?id=5">. Protected by AuthFilter. */
@WebServlet("/file")
public class FileServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        int id;
        try { id = Integer.parseInt(req.getParameter("id")); }
        catch (NumberFormatException e) { resp.sendError(400, "Bad file id"); return; }

        String path = null, type = null, name = null;
        try (Connection c = DBUtil.getConnection();
             PreparedStatement ps = c.prepareStatement(
                 "SELECT file_path, file_type, original_name FROM student_files WHERE id = ?")) {
            ps.setInt(1, id);
            try (ResultSet rs = ps.executeQuery()) {
                if (rs.next()) { path = rs.getString(1); type = rs.getString(2); name = rs.getString(3); }
            }
        } catch (SQLException e) { throw new ServletException(e); }

        if (path == null) { resp.sendError(404, "File not found"); return; }

        // Resolve inside the upload root and block ../ path traversal
        Path root = Paths.get(getServletContext().getInitParameter("uploadRoot")).toAbsolutePath().normalize();
        Path file = root.resolve(path).normalize();
        if (!file.startsWith(root) || !Files.isRegularFile(file)) { resp.sendError(404, "File not found"); return; }

        resp.setContentType(type);
        resp.setContentLengthLong(Files.size(file));
        resp.setHeader("Cache-Control", "private, max-age=3600");
        String disp = type.startsWith("image/") ? "inline" : "attachment";   // images show in <img>, PDFs download
        resp.setHeader("Content-Disposition", disp + "; filename=\"" + name.replaceAll("[\\r\\n\"]", "_") + "\"");
        Files.copy(file, resp.getOutputStream());                           // stream the bytes
    }
}
