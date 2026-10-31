package com.kpr.portal;

import java.io.IOException;
import java.sql.*;
import java.util.*;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.*;

/** Fetches students + file metadata with JDBC, then forwards to the JSP view. */
@WebServlet("/students")
public class StudentsServlet extends HttpServlet {
    @Override
    protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws ServletException, IOException {
        String q = req.getParameter("q") == null ? "" : req.getParameter("q").trim();
        Map<Integer, Student> map = new LinkedHashMap<>();   // keeps DB order

        String sql = "SELECT id, name, roll_no, email, course FROM students " +
                     "WHERE name LIKE ? OR roll_no LIKE ? OR course LIKE ? ORDER BY name";
        String fsql = "SELECT id, student_id, file_kind, original_name, file_type FROM student_files " +
                      "WHERE student_id = ? ORDER BY file_kind";
        try (Connection c = DBUtil.getConnection();
             PreparedStatement ps = c.prepareStatement(sql);
             PreparedStatement fps = c.prepareStatement(fsql)) {

            String like = "%" + q + "%";
            ps.setString(1, like); ps.setString(2, like); ps.setString(3, like);

            try (ResultSet rs = ps.executeQuery()) {
                while (rs.next()) {
                    map.put(rs.getInt("id"), new Student(rs.getInt("id"), rs.getString("name"),
                            rs.getString("roll_no"), rs.getString("email"), rs.getString("course")));
                }
            }
            // looping: attach each student's files
            for (Student s : map.values()) {
                fps.setInt(1, s.getId());
                try (ResultSet fr = fps.executeQuery()) {
                    while (fr.next()) {
                        s.getFiles().add(new Student.FileInfo(fr.getInt("id"), fr.getString("file_kind"),
                                fr.getString("original_name"), fr.getString("file_type")));
                    }
                }
            }
        } catch (SQLException e) {
            throw new ServletException("Could not load students", e);
        }
        req.setAttribute("students", new ArrayList<>(map.values()));
        req.setAttribute("q", q);
        req.getRequestDispatcher("/WEB-INF/views/students.jsp").forward(req, resp);
    }
}
