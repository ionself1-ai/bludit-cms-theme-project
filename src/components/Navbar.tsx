import { useState, useRef, useEffect } from "react";
import Icon from "@/components/ui/icon";

interface NavbarProps {
  theme: "light" | "dark";
  onToggleTheme: () => void;
  activePage: string;
  onNavigate: (page: string) => void;
}

const NAV_LINKS = [
  { id: "explore", label: "Explore" },
  { id: "categories", label: "Категории" },
  { id: "about", label: "Обо мне" },
  { id: "blog", label: "Блог" },
];

const MORE_LINKS = [
  { id: "privacy", label: "Privacy Policy", icon: "Shield" },
  { id: "terms", label: "Terms of Service", icon: "FileText" },
];

export default function Navbar({ theme, onToggleTheme, activePage, onNavigate }: NavbarProps) {
  const [moreOpen, setMoreOpen] = useState(false);
  const [searchOpen, setSearchOpen] = useState(false);
  const [profileOpen, setProfileOpen] = useState(false);
  const [searchValue, setSearchValue] = useState("");
  const moreRef = useRef<HTMLDivElement>(null);
  const profileRef = useRef<HTMLDivElement>(null);
  const searchRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    const handleClick = (e: MouseEvent) => {
      if (moreRef.current && !moreRef.current.contains(e.target as Node)) setMoreOpen(false);
      if (profileRef.current && !profileRef.current.contains(e.target as Node)) setProfileOpen(false);
    };
    document.addEventListener("mousedown", handleClick);
    return () => document.removeEventListener("mousedown", handleClick);
  }, []);

  useEffect(() => {
    if (searchOpen && searchRef.current) searchRef.current.focus();
  }, [searchOpen]);

  return (
    <header className="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur-sm">
      <div className="site-container">
        <div className="flex items-center justify-between h-14 gap-4">

          {/* Logo */}
          <button
            onClick={() => onNavigate("explore")}
            className="flex items-center gap-2 flex-shrink-0 hover:opacity-80 transition-opacity"
          >
            <div className="w-7 h-7 rounded-lg bg-foreground flex items-center justify-center">
              <span className="text-background text-xs font-bold">B</span>
            </div>
            <span className="font-semibold text-sm tracking-tight text-foreground hidden sm:block">Bludit</span>
          </button>

          {/* Nav links */}
          <nav className="flex items-center gap-0.5">
            {NAV_LINKS.map((link) => (
              <button
                key={link.id}
                onClick={() => onNavigate(link.id)}
                className={`px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150 ${
                  activePage === link.id
                    ? "text-foreground bg-secondary"
                    : "text-muted-foreground hover:text-foreground hover:bg-secondary/60"
                }`}
              >
                {link.label}
              </button>
            ))}

            {/* More dropdown */}
            <div className="relative" ref={moreRef}>
              <button
                onClick={() => setMoreOpen((v) => !v)}
                className={`flex items-center gap-1 px-3 py-1.5 rounded-md text-sm font-medium transition-colors duration-150 ${
                  moreOpen
                    ? "text-foreground bg-secondary"
                    : "text-muted-foreground hover:text-foreground hover:bg-secondary/60"
                }`}
              >
                More
                <Icon
                  name="ChevronDown"
                  size={13}
                  className={`transition-transform duration-200 ${moreOpen ? "rotate-180" : ""}`}
                />
              </button>

              {moreOpen && (
                <div className="absolute top-full left-0 mt-1.5 w-48 bg-popover border border-border rounded-xl shadow-lg py-1.5 animate-dropdown">
                  {MORE_LINKS.map((item) => (
                    <button
                      key={item.id}
                      onClick={() => { onNavigate(item.id); setMoreOpen(false); }}
                      className="dropdown-item w-full text-left"
                    >
                      <Icon name={item.icon} fallback="FileText" size={14} className="text-muted-foreground" />
                      {item.label}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </nav>

          {/* Right actions */}
          <div className="flex items-center gap-1">
            {/* Search */}
            <div className="flex items-center">
              {searchOpen ? (
                <div className="flex items-center gap-1 bg-secondary rounded-lg px-2 py-1">
                  <Icon name="Search" size={14} className="text-muted-foreground flex-shrink-0" />
                  <input
                    ref={searchRef}
                    value={searchValue}
                    onChange={(e) => setSearchValue(e.target.value)}
                    onKeyDown={(e) => e.key === "Escape" && setSearchOpen(false)}
                    placeholder="Поиск..."
                    className="bg-transparent text-sm outline-none w-36 placeholder:text-muted-foreground text-foreground"
                  />
                  <button onClick={() => { setSearchOpen(false); setSearchValue(""); }}>
                    <Icon name="X" size={13} className="text-muted-foreground hover:text-foreground transition-colors" />
                  </button>
                </div>
              ) : (
                <button
                  onClick={() => setSearchOpen(true)}
                  className="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-secondary transition-colors"
                >
                  <Icon name="Search" size={16} />
                </button>
              )}
            </div>

            {/* Theme toggle */}
            <button
              onClick={onToggleTheme}
              className="w-8 h-8 flex items-center justify-center rounded-lg text-muted-foreground hover:text-foreground hover:bg-secondary transition-colors"
              title={theme === "dark" ? "Светлая тема" : "Тёмная тема"}
            >
              <Icon name={theme === "dark" ? "Sun" : "Moon"} size={16} />
            </button>

            {/* Add button */}
            <button className="flex items-center gap-1.5 h-8 px-3 rounded-lg bg-foreground text-background text-sm font-medium hover:opacity-85 transition-opacity">
              <Icon name="Plus" size={14} />
              <span className="hidden sm:inline">Добавить</span>
            </button>

            {/* Profile */}
            <div className="relative" ref={profileRef}>
              <button
                onClick={() => setProfileOpen((v) => !v)}
                className="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-violet-500 flex items-center justify-center text-white text-xs font-semibold hover:opacity-85 transition-opacity"
              >
                A
              </button>

              {profileOpen && (
                <div className="absolute top-full right-0 mt-1.5 w-52 bg-popover border border-border rounded-xl shadow-lg py-1.5 animate-dropdown">
                  <div className="px-3 py-2 border-b border-border mb-1">
                    <p className="text-sm font-medium text-foreground">Alex Johnson</p>
                    <p className="text-xs text-muted-foreground">alex@example.com</p>
                  </div>
                  {[
                    { icon: "User", label: "Профиль" },
                    { icon: "Settings", label: "Настройки" },
                    { icon: "BookOpen", label: "Мои записи" },
                    { icon: "Bookmark", label: "Закладки" },
                  ].map((item) => (
                    <button key={item.label} className="dropdown-item w-full text-left">
                      <Icon name={item.icon} fallback="User" size={14} className="text-muted-foreground" />
                      {item.label}
                    </button>
                  ))}
                  <div className="border-t border-border mt-1 pt-1">
                    <button className="dropdown-item w-full text-left text-destructive hover:bg-destructive/10">
                      <Icon name="LogOut" size={14} />
                      Выйти
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </header>
  );
}