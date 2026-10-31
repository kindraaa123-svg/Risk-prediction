export type SharedProps = {
    app: { name: string; url: string };
    systemSettings: { websiteName: string; websiteShortName: string; websiteDescription: string; websiteTagline: string; logoUrl: string | null; faviconUrl: string | null; contactEmail: string; contactPhone: string; contactWhatsapp: string; companyAddress: string; companyCity: string; companyProvince: string; companyPostalCode: string; companyCountry: string };
    auth: { user: { id: number; name: string; email: string; status: string } | null; roles: string[]; permissions: string[] };
    demo: { enabled: boolean; accounts: Record<string, { email: string; password: string }> };
    flash: { success?: string; error?: string };
};

export type DashboardProps = SharedProps & {
    metrics: {
        users: number;
        projects: number;
        highRiskProjects: number;
        activities: number;
        projectStatus: { status: string; total: number }[];
        taskStatus: { status: string; total: number }[];
    };
};
