package com.kpr.portal;

import java.util.ArrayList;
import java.util.List;

/** Simple bean (getters are used by JSP Expression Language). */
public class Student {
    private int id;
    private String name, rollNo, email, course;
    private final List<FileInfo> files = new ArrayList<>();

    public Student(int id, String name, String rollNo, String email, String course) {
        this.id = id; this.name = name; this.rollNo = rollNo; this.email = email; this.course = course;
    }
    public int getId() { return id; }
    public String getName() { return name; }
    public String getRollNo() { return rollNo; }
    public String getEmail() { return email; }
    public String getCourse() { return course; }
    public List<FileInfo> getFiles() { return files; }

    /** Metadata of one uploaded file. */
    public static class FileInfo {
        private int id; private String kind, originalName, type;
        public FileInfo(int id, String kind, String originalName, String type) {
            this.id = id; this.kind = kind; this.originalName = originalName; this.type = type;
        }
        public int getId() { return id; }
        public String getKind() { return kind; }
        public String getOriginalName() { return originalName; }
        public String getType() { return type; }
        public boolean isImage() { return type != null && type.startsWith("image/"); }
    }
}
